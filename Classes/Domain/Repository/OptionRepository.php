<?php

namespace FGTCLB\T3oodle\Domain\Repository;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\Option;
use FGTCLB\T3oodle\Domain\Model\Poll;
use FGTCLB\T3oodle\Utility\ScheduleOptionUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class OptionRepository extends Repository
{
    protected $defaultOrderings = ['sorting' => 'ASC'];

    /**
     * @return QueryResultInterface|Option[]|null
     */
    public function findByPollAndCreatorIdent(Poll $poll, string $creatorIdent): ?QueryResultInterface
    {
        if (empty($creatorIdent)) {
            return null;
        }
        $query = $this->createQuery();
        $query->matching($query->logicalAnd([
            $query->equals('poll', $poll),
            $query->equals('creatorIdent', $creatorIdent),
        ]));

        return $query->execute();
    }

    public function updateSortingOfOptionsByDateTime(Poll $poll): void
    {
        $options = $poll->getOptions()->toArray();
        usort($options, static function (Option $a, Option $b) {
            $a2 = ScheduleOptionUtility::parseOptionName($a->getName())['dateStart'];
            $b2 = ScheduleOptionUtility::parseOptionName($b->getName())['dateStart'];
            if ($a2 > $b2) {
                return 1;
            }

            return -1;
        });

        $i = 1;
        /** @var Option $option */
        foreach ($options as $option) {
            $option->setSorting($i);
            $option->getUid() ? $this->update($option) : $this->add($option);
            $i *= 2;
        }
    }
}
