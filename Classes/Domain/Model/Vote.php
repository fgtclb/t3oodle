<?php
namespace T3\T3oodle\Domain\Model;


use T3\T3oodle\Utility\SettingsUtility;

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
 * Vote
 */
class Vote extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    protected $participant;

    /**
     * @var string
     */
    protected $participantName = '';

    /**
     * @var string
     */
    protected $participantMail = '';

    /**
     * @var string
     */
    protected $participantIdent = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\T3oodle\Domain\Model\OptionValue>
     */
    protected $optionValues;

    /**
     * @var \T3\T3oodle\Domain\Model\Poll
     */
    protected $parent = null;


    public function __construct()
    {
        $this->optionValues = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    public function getParticipant(): ?\TYPO3\CMS\Extbase\Domain\Model\FrontendUser
    {
        return $this->participant;
    }

    public function setParticipant(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $participant): void
    {
        $this->participant = $participant;
    }

    public function getParticipantName(): string
    {
        if ($this->getParticipant()) {
            $getter = 'name';
            $settings = SettingsUtility::getTypoScriptSettings();
            if ($settings && $settings['frontendUserNameField']) {
                $getter = $settings['frontendUserNameField'] ?? 'name';
            }
            $getter = 'get' . ucfirst($getter);
            return $this->getParticipant()->$getter();
        }
        return $this->participantName;
    }

    public function setParticipantName(string $participantName): void
    {
        $this->participantName = $participantName;
    }

    public function getParticipantMail(): string
    {
        if ($this->getParticipant()) {
            $getter = 'email';
            $settings = SettingsUtility::getTypoScriptSettings();
            if ($settings && $settings['frontendUserMailField']) {
                $getter = $settings['frontendUserMailField'] ?? 'email';
            }
            $getter = 'get' . ucfirst($getter);
            return $this->getParticipant()->$getter();
        }
        return $this->participantMail;
    }

    public function setParticipantMail(string $participantMail): void
    {
        $this->participantMail = $participantMail;
    }

    public function getParticipantIdent(): string
    {
        return $this->participantIdent;
    }

    public function setParticipantIdent(string $participantIdent): void
    {
        $this->participantIdent = $participantIdent;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage|OptionValue[]
     */
    public function getOptionValues(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->optionValues;
    }

    public function setOptionValues(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $optionValues): void
    {
        $this->optionValues = $optionValues;
    }

    public function addOptionValue(OptionValue $optionValue): void
    {
        $this->optionValues->attach($optionValue);
    }

    public function removeOptionValue(OptionValue $optionValue): void
    {
        $this->optionValues->detach($optionValue);
    }

    public function getParent(): ?Poll
    {
        return $this->parent;
    }

    public function setParent(\T3\T3oodle\Domain\Model\Poll $parent): void
    {
        $this->parent = $parent;
    }
}
