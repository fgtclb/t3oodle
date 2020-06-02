<?php
namespace T3\T3oodle\Domain\Model;

use T3\T3oodle\Traits\Model\MarkToDeleteTrait;
use T3\T3oodle\Utility\UserIdentUtility;

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
 * Option
 */
class Option extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    use MarkToDeleteTrait;

    /**
     * name
     * 
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $name = '';

    /**
     * selected
     * 
     * @var bool
     */
    protected $selected = false;


    /**
     * poll
     * 
     * @var \T3\T3oodle\Domain\Model\Poll
     */
    protected $poll = null;

    /**
     * Returns the name
     * 
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     * 
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the selected
     * 
     * @return bool $selected
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * Sets the selected
     * 
     * @param bool $selected
     * @return void
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;
    }

    /**
     * Returns the boolean state of selected
     * 
     * @return bool
     */
    public function isSelected()
    {
        return $this->selected;
    }

    /**
     * @return \T3\T3oodle\Domain\Model\Poll
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @return void
     */
    public function setPoll(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->poll = $poll;
    }

    public function getCheckboxStates(): array
    {
        $states = [
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
