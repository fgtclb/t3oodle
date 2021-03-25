<?php

namespace FGTCLB\T3oodle\Domain\Model;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Traits\Model\CreatorTrait;
use FGTCLB\T3oodle\Traits\Model\MarkToDeleteTrait;
use FGTCLB\T3oodle\Traits\Model\RecordDatePropertiesTrait;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Option extends AbstractEntity
{
    use MarkToDeleteTrait;
    use RecordDatePropertiesTrait;
    use CreatorTrait;

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $name = '';

    /**
     * @var int
     */
    protected $sorting = 0;

    /**
     * @var \FGTCLB\T3oodle\Domain\Model\Poll
     */
    protected $poll;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(?int $sorting): void
    {
        $this->sorting = (int)$sorting;
    }

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(Poll $poll): void
    {
        $this->poll = $poll;
    }

    public function getCheckboxStates(): array
    {
        $states = [
            -1 => '',
            0 => 'no',
            1 => 'yes',
        ];
        if ($this->poll && $this->poll->isSettingTristateCheckbox()) {
            $states[2] = 'maybe';
        }

        return $states;
    }

    public function isFull(): bool
    {
        $votesLeft = $this->getAmountOfLeftVotes() ?? 1;

        return 0 === $votesLeft;
    }

    public function getAmountOfLeftVotes(bool $respectCurrentUser = false): ?int
    {
        if (!$this->getPoll()->getSettingMaxVotesPerOption()) {
            return null;
        }

        $total = 0;
        foreach ($this->getPoll()->getVotes() as $vote) {
            if ($respectCurrentUser || $vote->getParticipantIdent() !== UserIdentUtility::getCurrentUserIdent()) {
                foreach ($vote->getOptionValues() as $optionValue) {
                    if ($optionValue->getOption() === $this) {
                        if ('1' === $optionValue->getValue()) {
                            ++$total;
                        }
                    }
                }
            }
        }

        return $this->getPoll()->getSettingMaxVotesPerOption() - $total;
    }

    public function getAmountOfLeftVotesRespectingCurrentUser(): ?int
    {
        return $this->getAmountOfLeftVotes(true);
    }
}
