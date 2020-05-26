<?php
namespace T3\T3oodle\Domain\Repository;


use T3\T3oodle\Domain\Model\Option;
use T3\T3oodle\Domain\Model\Poll;
use T3\T3oodle\Domain\Model\Vote;
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
 * The repository for Votes
 */
class VoteRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    public function findByPollAndParticipantIdent(Poll $poll, string $participantIdent): ?Vote
    {
        if (empty($participantIdent)) {
            return null;
        }

        $query = $this->createQuery();
        $query->matching($query->logicalAnd([
            $query->equals('parent', $poll),
            $query->equals('participantIdent', $participantIdent)
        ]));
        return $query->execute()->getFirst();
    }

}
