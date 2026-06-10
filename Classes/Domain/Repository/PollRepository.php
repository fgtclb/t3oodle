<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Domain\Repository;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

use Doctrine\DBAL\Exception;
use FGTCLB\T3oodle\Domain\Enumeration\Visibility;
use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Event\PollRepository\FindPollsEvent;
use FGTCLB\T3oodle\Service\UserService;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<BasePoll>
 */
final class PollRepository extends Repository
{
    protected EventDispatcherInterface $eventDispatcher;
    private UserService $userService;

    protected $objectType = BasePoll::class;

    /**
     * @var array<non-empty-string, 'ASC'|'DESC'> Show unpublished first, then order by publishDate
     */
    protected $defaultOrderings = [
        'isPublished' => 'ASC',
        'publishDate' => 'DESC',
    ];

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
        parent::__construct();
        $this->objectType = BasePoll::class;
    }

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function injectUserService(UserService $userService): void
    {
        $this->userService = $userService;
    }

    /**
     * @return QueryResultInterface<BasePoll>
     * @throws InvalidConfigurationTypeException
     */
    public function findPolls(
        bool $draft,
        bool $finished,
        bool $personal
    ): QueryResultInterface {
        /** @var QueryInterface<BasePoll> $query */
        $query = $this->createQuery();
        $orConstraints = [];

        if ($draft) {
            $orConstraints[] = $query->equals('isPublished', false);
        }
        $orConstraints[] = $query->logicalAnd(
            $query->equals('isPublished', true),
            $query->equals('isFinished', false),
        );
        if ($finished) {
            $orConstraints[] = $query->equals('isFinished', true);
        }

        $andConstraints = [];

        if ($personal) {
            if (!$this->userService->userIsAdmin()) {
                $andConstraints[] = $query->logicalOr(
                    $query->logicalAnd(
                        $query->equals('visibility', Visibility::LISTED),
                        $query->equals('isPublished', true),
                    ),
                    $query->equals('authorIdent', UserIdentUtility::getCurrentUserIdent()),
                );
            }
        } else {
            $andConstraints[] = $query->equals('visibility', Visibility::LISTED);
            $andConstraints[] = $query->equals('isPublished', true);
        }

        $andConstraints[] = $query->logicalOr(...$orConstraints);

        $andConstraints[] = $query->logicalNot($query->equals('slug', ''));

        $event = new FindPollsEvent($andConstraints, $query, $this);
        $this->eventDispatcher->dispatch($event);

        $andConstraints = $event->getConstraints();

        $query->matching($query->logicalAnd(...$andConstraints));

        return $query->execute();
    }

    /**
     * Retrieves the poll type by its unique identifier (UID).
     *
     * @throws \RuntimeException If the poll is not found.
     * @throws Exception
     */
    public function getPollTypeByUid(int $poll): string
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_t3oodle_domain_model_poll');
        $result = $queryBuilder
            ->select('type')
            ->from('tx_t3oodle_domain_model_poll')
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($poll, Connection::PARAM_INT)
            ))
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if ($result === false) {
            throw new \RuntimeException('Poll not found', 1730287624);
        }

        return $result['type'];
    }

    public function countBySlug(string $slug): int
    {
        $query = $this->createQuery();
        return $query
            ->matching($query->equals('slug', $slug))
            ->execute()
            ->count();
    }
}
