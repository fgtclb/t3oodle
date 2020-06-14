<?php
namespace T3\T3oodle\Domain\Repository;

use T3\T3oodle\Domain\Enumeration\Visibility;
use T3\T3oodle\Domain\Permission\PollPermission;
use T3\T3oodle\Utility\DateTimeUtility;
use T3\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/***
 *
 * This file is part of the "t3oodle" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020
 *
 ***/
/**
 * The repository for Polls
 */
class PollRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @var string[] Show unpublished first, then order by publishDate
     */
    protected $defaultOrderings = [
        'isPublished' => 'ASC',
        'publishDate' => 'DESC'
    ];

    public function findPolls(
        bool $draft,
        bool $opened,
        bool $closed,
        bool $finished,
        bool $personal
    ): QueryResultInterface {
        $query = $this->createQuery();
        $orConstraints = [];

        if ($draft) {
            $orConstraints[] = $query->equals('isPublished', false);
        }
        if ($opened) {
            $orConstraints[] = $query->logicalAnd([
                $query->equals('isPublished', true),
                $query->equals('isFinished', false),
                // not expired,
                $query->logicalOr([
                    $query->equals('settingVotingExpiresDate', 0),
                    $query->greaterThanOrEqual('settingVotingExpiresDate', DateTimeUtility::today()->getTimestamp()),
                ]),
                $query->logicalOr([
                    $query->equals('settingVotingExpiresTime', 0),
                    $query->greaterThanOrEqual('settingVotingExpiresTime', DateTimeUtility::time()->getTimestamp()),
                ]),
                // TODO: amount of available options greater than 0
            ]);
        }
        if ($closed) {
            $orConstraints[] = $query->logicalAnd([
                $query->equals('isPublished', true),
                $query->equals('isFinished', false),
                $query->greaterThan('settingVotingExpiresDate', 0),
                $query->greaterThan('settingVotingExpiresTime', 0),
                $query->logicalOr([
                    $query->lessThan('settingVotingExpiresDate', DateTimeUtility::today()->getTimestamp()),
                    $query->lessThan('settingVotingExpiresTime', DateTimeUtility::time()->getTimestamp()),
                ])
                // TODO: no available options given
            ]);
        }
        if ($finished) {
            $orConstraints[] = $query->equals('isFinished', true);
        }

        $andConstraints = [];

        $pollPermission = GeneralUtility::makeInstance(PollPermission::class);
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
}
