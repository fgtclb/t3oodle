<?php

namespace FGTCLB\T3oodle\Domain\Repository;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Enumeration\Visibility;
use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Domain\Permission\PollPermission;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class PollRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @var string[] Show unpublished first, then order by publishDate
     */
    protected $defaultOrderings = [
        'isPublished' => 'ASC',
        'publishDate' => 'DESC',
    ];

    public function __construct(ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);
        $this->objectType = BasePoll::class;
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
        $orConstraints[] = $query->logicalAnd([
            $query->equals('isPublished', true),
            $query->equals('isFinished', false),
        ]);
        if ($finished) {
            $orConstraints[] = $query->equals('isFinished', true);
        }

        $andConstraints = [];

        $pollPermission = GeneralUtility::makeInstance(PollPermission::class, null, $this->controllerSettings);
        if ($personal) {
            if (!$pollPermission->userIsAdmin()) {
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

        if (!empty($orConstraints)) {
            $andConstraints[] = $query->logicalOr($orConstraints);
        }

        $andConstraints[] = $query->logicalNot($query->equals('slug', ''));

        /** @var Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $slot = $signalSlotDispatcher->dispatch(__CLASS__, 'findPolls', [
            'constraints' => $andConstraints,
            'query' => $query,
            'caller' => $this,
        ]);
        $andConstraints = $slot['constraints'];

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
        $result = $pool->getQueryBuilderForTable('tx_t3oodle_domain_model_poll')
            ->select('type')
            ->from('tx_t3oodle_domain_model_poll')
            ->where('uid = ' . $poll)
            ->execute()
            ->fetch();

        return $result['type'];
    }
}
