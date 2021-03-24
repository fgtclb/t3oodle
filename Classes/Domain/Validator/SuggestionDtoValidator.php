<?php declare(strict_types=1);
namespace FGTCLB\T3oodle\Domain\Validator;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Enumeration\PollType;
use FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto;
use FGTCLB\T3oodle\Domain\Model\Option;
use FGTCLB\T3oodle\Utility\ScheduleOptionUtility;
use FGTCLB\T3oodle\Utility\TranslateUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class SuggestionDtoValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    /**
     * @param SuggestionDto $value
     * @return bool
     */
    protected function isValid($value)
    {
        $isValid = true;
        $suggestion = trim($value->getSuggestion());

        // Check participant
        if (!$value->getCreator()) {
            if (empty(trim($value->getCreatorName()))) {
                $isValid = false;

                $this->result->forProperty('creatorName')->addError(
                    new Error(TranslateUtility::translate('validation.1616519320'), 1616519320)
                );
            }
            if (empty(trim($value->getCreatorMail()))) {
                $isValid = false;
                $this->result->forProperty('creatorMail')->addError(
                    new Error(TranslateUtility::translate('validation.1616519321'), 1616519321)
                );
            }
            if ($value->getCreatorMail() && !GeneralUtility::validEmail($value->getCreatorMail())) {
                $isValid = false;
                $this->result->forProperty('creatorMail')->addError(
                    new Error(TranslateUtility::translate('validation.1616519322'), 1616519322)
                );
            }
        }

        if (empty($suggestion)) {
            $isValid = false;
            $this->result->forProperty('suggestion')->addError(
                new Error(TranslateUtility::translate('validation.1616516640', []), 1616516640)
            );
        }

        /** @var Option $option */
        foreach ($value->getPoll()->getOptions() as $option) {
            if ($option->getName() === $suggestion) {
                $isValid = false;
                $this->result->forProperty('suggestion')->addError(
                    new Error(TranslateUtility::translate('validation.1616516641', [$suggestion]), 1616516641)
                );
                break;
            }
        }

        if ($value->getPoll()->getType() === PollType::SCHEDULE) {
            $validationErrors = ScheduleOptionUtility::validateOptionName($suggestion);
            foreach ($validationErrors as $validationError) {
                $this->result->forProperty('suggestion')->addError($validationError);
            }
        }

        return $isValid;
    }
}
