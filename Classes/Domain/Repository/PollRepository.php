<?php

namespace FGTCLB\T3oodle\Domain\Repository;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Enumeration\Visibility;
use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Event\PollRepository\FindPollsEvent;
use FGTCLB\T3oodle\Service\UserService;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class PollRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    private UserService $userService;

    /**
     * @var array<non-empty-string, 'ASC'|'DESC'> Show unpublished first, then order by publishDate
     */
    protected $defaultOrderings = [
        'isPublished' => 'ASC',
        'publishDate' => 'DESC',
    ];

    public function __construct(ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);
        $this->objectType = BasePoll::class;
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $this->userService = GeneralUtility::makeInstance(UserService::class);
    }

    /**
     * @var array
     */
    private $controllerSettings = [];

    public function findPolls(
        bool $draft,
        bool $finished,
        bool $personal
    ): QueryResultInterface {
        $query = $this->createQuery();
        $orConstraints = [];

        if ($draft) {
            $orConstraints[] = $query->equals('isPublished', false);
        }
        $orConstraints[] = $query->logicalAnd([
            $query->equals('isPublished', true),
            $query->equals('isFinished', false),
        ]);
        if ($finished) {
            $orConstraints[] = $query->equals('isFinished', true);
        }

        $andConstraints = [];

        if ($personal) {
            if (!$this->userService->userIsAdmin()) {
                $andConstraints[] = $query->logicalOr([
                    $query->logicalAnd([
                        $query->equals('visibility', Visibility::LISTED),
                        $query->equals('isPublished', true),
                    ]),
                    $query->equals('authorIdent', UserIdentUtility::getCurrentUserIdent()),
                ]);
            }
        } else {
            $andConstraints[] = $query->equals('visibility', Visibility::LISTED);
            $andConstraints[] = $query->equals('isPublished', true);
        }

        if ($orConstraints !== []) {
            $andConstraints[] = $query->logicalOr($orConstraints);
        }

        $andConstraints[] = $query->logicalNot($query->equals('slug', ''));

        $event = new FindPollsEvent($andConstraints, $query, $this);
        $this->eventDispatcher->dispatch($event);

        $andConstraints = $event->getConstraints();

        $query->matching($query->logicalAnd($andConstraints));

        return $query->execute();
    }

    public function setControllerSettings(array $settings): void
    {
        $this->controllerSettings = $settings;
    }

    public function getPollTypeByUid(int $poll): string
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $pool->getQueryBuilderForTable('tx_t3oodle_domain_model_poll');
        $result = $queryBuilder
            ->select('type')
            ->from('tx_t3oodle_domain_model_poll')
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($poll, Connection::PARAM_INT)
            ))
            ->executeQuery()
            ->fetchAssociative();

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
