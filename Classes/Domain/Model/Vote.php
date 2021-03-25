<?php

namespace FGTCLB\T3oodle\Domain\Model;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Traits\Model\DynamicUserProperties;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Vote extends AbstractEntity
{
    use DynamicUserProperties;

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
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FGTCLB\T3oodle\Domain\Model\OptionValue>
     */
    protected $optionValues;

    /**
     * @var \FGTCLB\T3oodle\Domain\Model\Poll
     */
    protected $poll;

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
            return $this->getPropertyDynamically($this->getParticipant(), 'name');
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
            return $this->getPropertyDynamically($this->getParticipant(), 'mail', false);
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

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(Poll $poll): void
    {
        $this->poll = $poll;
    }
}
