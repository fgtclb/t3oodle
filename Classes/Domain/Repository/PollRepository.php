<?php
namespace FGTCLB\T3oodle\Domain\Repository;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Enumeration\Visibility;
use FGTCLB\T3oodle\Domain\Permission\PollPermission;
use FGTCLB\T3oodle\Utility\DateTimeUtility;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class PollRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @var string[] Show unpublished first, then order by publishDate
     */
    protected $defaultOrderings = [
        'isPublished' => 'ASC',
        'publishDate' => 'DESC'
    ];

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
                    $query->equals('authorIdent', UserIdentUtility::getCurrentUserIdent())
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
        $query->matching($query->logicalAnd($andConstraints));
        return $query->execute();
    }

    public function setControllerSettings(array $settings): void
    {
        $this->controllerSettings = $settings;
    }
}
