<?php declare(strict_types=1);
namespace T3\T3oodle\Domain\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class PollValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $value
     * @return bool
     */
    protected function isValid($value)
    {
        if (!$value) {
            return true;
        }
        $isValid = true;
        $optionsUnique = true;
        $optionValues = [];

        $options = $value->getOptions(true);

        // Check options count
        if (count($options) < 2) {
            $isValid = false;
            $this->result->forProperty('options')->addError(
                new Error('At least two options are required.', 52)
            );
        }

        // Check unique options
        /** @var \T3\T3oodle\Domain\Model\Option $option */
        foreach ($options as $key => $option) {
            if (in_array($option->getName(), $optionValues)) {
                $this->result->forProperty('options.' . $key . '.name')->addError(
                    new Error('The option value "%s" is already used in another option.', 55, [$option->getName()])
                );
                $optionsUnique = false;
            }
            $optionValues[] = $option->getName();
        }
        if (!$optionsUnique) {
            $isValid = false;
            $this->result->forProperty('options')->addError(
                new Error('All options in a poll must be unique.', 51)
            );
        }

        // Check title
        if (empty(trim($value->getTitle()))) {
            $isValid = false;
            $this->result->forProperty('title')->addError(
                new Error('Title is required!', 59)
            );
        }

        // Check author
        if (!$value->getAuthor()) {
            if (empty(trim($value->getAuthorName()))) {
                $isValid = false;
                $this->result->forProperty('authorName')->addError(
                    new Error('Author name is required!', 59)
                );
            }
            if (empty(trim($value->getAuthorMail()))) {
                $isValid = false;
                $this->result->forProperty('authorMail')->addError(
                    new Error('Author mail is required!', 60)
                );
            }
            if ($value->getAuthorMail() && !GeneralUtility::validEmail($value->getAuthorMail())) {
                $isValid = false;
                $this->result->forProperty('authorMail')->addError(
                    new Error('Author mail is no valid mail address!', 61)
                );
            }
        }

        // Check settings
        if ($value->getSettingMaxVotesPerOption() < 0) {
            $isValid = false;
            $this->result->forProperty('settingMaxVotesPerOption')->addError(
                new Error('Max votes per option must be a positive number or zero.', 61)
            );
        }

        if ($value->getSettingVotingExpiresDate()) {
            $today = (new \DateTime())->setTimestamp($GLOBALS['SIM_EXEC_TIME'])->modify('midnight');
            if ($value->getSettingVotingExpiresDate()->getTimestamp() < $today->getTimestamp()) {
                $isValid = false;
                $this->result->forProperty('settingVotingExpiresDate')->addError(
                    new Error('The expiration date must be located in the future.', 61)
                );
            }

            if (!$value->getSettingVotingExpiresTime()) {
                $isValid = false;
                $this->result->forProperty('settingVotingExpiresTime')->addError(
                    new Error('You also need to define a time for voting expiration.', 62)
                );
            }

            if ($expiresAt = $value->getSettingVotingExpiresAt()) {
                $now = (new \DateTime())->setTimestamp($GLOBALS['SIM_EXEC_TIME']);
                if ($expiresAt->getTimestamp() < $now->getTimestamp()) {
                    $isValid = false;
                    $this->result->forProperty('settingVotingExpiresTime')->addError(
                        new Error('The expiration date (including the time) must be located in the future.', 63)
                    );
                }
            }
        }

        return $isValid;
    }
}
