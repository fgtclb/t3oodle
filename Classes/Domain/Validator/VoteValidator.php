<?php declare(strict_types=1);
namespace T3\T3oodle\Domain\Validator;

use T3\T3oodle\Utility\TranslateUtility;
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
                    new Error(TranslateUtility::translate('validation.1592143020'), 1592143020)
                );
            }
            if (empty(trim($value->getParticipantMail()))) {
                $isValid = false;
                $this->result->forProperty('participantMail')->addError(
                    new Error(TranslateUtility::translate('validation.1592143021'), 1592143021)
                );
            }
            if ($value->getParticipantMail() && !GeneralUtility::validEmail($value->getParticipantMail())) {
                $isValid = false;
                $this->result->forProperty('participantMail')->addError(
                    new Error(TranslateUtility::translate('validation.1592143022'), 1592143022)
                );
            }
        }

        // Check poll settings
        // One option only
        if ($value->getPoll() && $value->getPoll()->isSettingOneOptionOnly()) {
            $amount = 0;
            foreach ($value->getOptionValues() as $optionValue) {
                if ($optionValue->getValue() !== '0') {
                    $amount++;
                }
            }
            if ($amount > 1) {
                $isValid = false;
                $this->result->forProperty('optionValues')->addError(
                    new Error(TranslateUtility::translate('validation.1592143023'), 1592143023)
                );
            }
        }

        // Max votes per option
        if ($value->getPoll() && $value->getPoll()->getSettingMaxVotesPerOption() > 0) {
            foreach ($value->getOptionValues() as $optionValue) {
                if ($optionValue->getValue() !== '0' &&
                    $optionValue->getOption() &&
                    $optionValue->getOption()->isFull()
                ) {
                    $isValid = false;
                    $this->result->forProperty('optionValues')->addError(
                        new Error(
                            TranslateUtility::translate(
                                'validation.1592143024',
                                [$optionValue->getOption()->getName()]
                            ),
                            1592143024
                        )
                    );
                }
            }
        }
        return $isValid;
    }
}
