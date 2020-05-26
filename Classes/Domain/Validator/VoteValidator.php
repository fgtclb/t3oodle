<?php declare(strict_types=1);
namespace T3\T3oodle\Domain\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class VoteValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = true;

    /**
     * @param \T3\T3oodle\Domain\Model\Vote $value
     * @return bool
     */
    protected function isValid($value)
    {
        $isValid = true;

        // Check participant
        if (!$value->getParticipant()) {
            if (empty(trim($value->getParticipantName()))) {
                $isValid = false;
                $this->result->forProperty('participantName')->addError(
                    new Error('Participant name is required!', 59)
                );
            }
            if (empty(trim($value->getParticipantMail()))) {
                $isValid = false;
                $this->result->forProperty('participantMail')->addError(
                    new Error('Participant mail is required!', 60)
                );
            }
            if ($value->getParticipantMail() && !GeneralUtility::validEmail($value->getParticipantMail())) {
                $isValid = false;
                $this->result->forProperty('participantMail')->addError(
                    new Error('Participant mail is no valid mail address!', 61)
                );
            }
        }

        // Check poll settings
        // One option only
        if ($value->getParent() && $value->getParent()->isSettingOneOptionOnly()) {
            $amount = 0;
            foreach ($value->getOptionValues() as $optionValue) {
                if ($optionValue->getValue() !== '0') {
                    $amount++;
                }
            }
            if ($amount > 1) {
                $isValid = false;
                $this->result->forProperty('optionValues')->addError(
                    new Error('Only one option per participant is allowed!', 61)
                );
            }
        }

        // Max votes per option
        if ($value->getParent() && $value->getParent()->getSettingMaxVotesPerOption() > 0) {
            foreach ($value->getOptionValues() as $optionValue) {
                if ($optionValue->getValue() !== '0' && $optionValue->getOption()->isFull()) {
                    $isValid = false;
                    $this->result->forProperty('optionValues')->addError(
                        new Error('Sorry, the option "%s" is already full.', 61, [$optionValue->getOption()->getName()])
                    );
                }
            }
        }
        return $isValid;
    }
}
