<?php
namespace T3\T3oodle\Domain\Model;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */
use T3\T3oodle\Traits\Model\MarkToDeleteTrait;
use T3\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Option extends AbstractEntity
{
    use MarkToDeleteTrait;

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $name = '';

    /**
     * @var \T3\T3oodle\Domain\Model\Poll
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

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(\T3\T3oodle\Domain\Model\Poll $poll): void
    {
        $this->poll = $poll;
    }

    public function getCheckboxStates(): array
    {
        $states = [
            -1 => '',
            0 => 'no',
            1 => 'yes'
        ];
        if ($this->poll && $this->poll->isSettingTristateCheckbox()) {
            $states[2] = 'maybe';
        }
        return $states;
    }

    public function isFull(): bool
    {
        if (!$this->getPoll()->getSettingMaxVotesPerOption()) {
            return false;
        }

        $total = 0;
        foreach ($this->getPoll()->getVotes() as $vote) {
            if ($vote->getParticipantIdent() !== UserIdentUtility::getCurrentUserIdent()) {
                foreach ($vote->getOptionValues() as $optionValue) {
                    if ($optionValue->getOption() === $this) {
                        if ($optionValue->getValue() === '1') {
                            $total++;
                        }
                    }
                }
            }
        }
        return $total >= $this->getPoll()->getSettingMaxVotesPerOption();
    }
}
