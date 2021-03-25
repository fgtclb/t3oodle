<?php

namespace FGTCLB\T3oodle\Domain\Repository;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\Poll;
use FGTCLB\T3oodle\Domain\Model\Vote;

class VoteRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    public function findOneByPollAndParticipantIdent(Poll $poll, string $participantIdent): ?Vote
    {
        if (empty($participantIdent)) {
            return null;
        }
        $query = $this->createQuery();
        $query->matching($query->logicalAnd([
            $query->equals('poll', $poll),
            $query->equals('participantIdent', $participantIdent),
        ]));

        return $query->execute()->getFirst();
    }
}
