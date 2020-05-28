<?php
namespace T3\T3oodle\Domain\Repository;


use T3\T3oodle\Utility\UserIdentUtility;
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

    public function findPolls(): QueryResultInterface
    {
        $query = $this->createQuery();
        $constraints = [];
        if (true) { // TODO: check if current fe_user is not an admin
            $constraints = [
                $query->logicalOr([
                    $query->logicalAnd([
                        $query->equals('visibility', 'listed'),
                        $query->equals('isPublished', true),
                    ]),
                    $query->equals('authorIdent', UserIdentUtility::getCurrentUserIdent())
                ])
            ];
        }
        $constraints[] = $query->logicalNot($query->equals('slug', ''));
        $query->matching($query->logicalAnd($constraints));
        return $query->execute();
    }
}
