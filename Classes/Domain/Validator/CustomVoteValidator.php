<?php

declare(strict_types = 1);

namespace FGTCLB\T3oodle\Domain\Validator;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Utility\TranslateUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class CustomVoteValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = true;

    /**
     * @param \FGTCLB\T3oodle\Domain\Model\Vote $value
     *
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
        // Max votes per participant
        if ($value->getPoll() && $value->getPoll()->getSettingMaxVotesPerParticipant()) {
            $amount = 0;
            foreach ($value->getOptionValues() as $optionValue) {
                if ('0' !== $optionValue->getValue()) {
                    ++$amount;
                }
            }
            if ($amount > $value->getPoll()->getSettingMaxVotesPerParticipant()) {
                $isValid = false;
                $this->result->forProperty('optionValues')->addError(
                    new Error(
                        TranslateUtility::translate(
                            'validation.1592143023',
                            [$value->getPoll()->getSettingMaxVotesPerParticipant()]
                        ),
                        1592143023
                    )
                );
            }
        }

        // Max votes per option
        if ($value->getPoll() && $value->getPoll()->getSettingMaxVotesPerOption() > 0) {
            foreach ($value->getOptionValues() as $optionValue) {
                if ('0' !== $optionValue->getValue() &&
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
