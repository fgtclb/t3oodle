<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Domain\Repository;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Domain\Model\Vote;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Vote>
 */
final class VoteRepository extends Repository
{
    public function findOneByPollAndParticipantIdent(BasePoll $poll, string $participantIdent): ?Vote
    {
        if ($participantIdent === '' || $participantIdent === '0') {
            return null;
        }
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('poll', $poll),
                $query->equals('participantIdent', $participantIdent),
            )
        );

        return $query->execute()->getFirst();
    }
}
