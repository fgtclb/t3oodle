<?php declare(strict_types=1);
namespace T3\T3oodle\Domain\Validator;

use T3\T3oodle\Domain\Enumeration\Visibility;
use T3\T3oodle\Utility\DateTimeUtility;
use TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class PollValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    protected $supportedOptions = [
        'action' => ['update', '"create" or "update"', 'string'],
    ];

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
        $i = 0;
        /** @var \T3\T3oodle\Domain\Model\Option $option */
        foreach ($options as $key => $option) {
            if ($this->options['action'] === 'create') {
                $key = $i++;
            }
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
        if (strlen($value->getTitle()) > 255) {
            $isValid = false;
            $this->result->forProperty('title')->addError(
                new Error('Maximum title length is 255 chars', 59)
            );
        }

        if ($value->getDescription() && strlen($value->getDescription()) > 65535) {
            $isValid = false;
            $this->result->forProperty('description')->addError(
                new Error('Maximum description length is 65535 chars', 59)
            );
        }

        if ($value->getLink() && filter_var($value->getLink(), FILTER_VALIDATE_URL) === false) {
            $isValid = false;
            $this->result->forProperty('link')->addError(
                new Error('Given link is not a valid URL.', 59)
            );
        }
        if ($value->getLink() && strpos($value->getLink(), 'http') !== 0) {
            $isValid = false;
            $this->result->forProperty('link')->addError(
                new Error('Must start with http:// or https://', 59)
            );
        }
        try {
            new Visibility($value->getVisibility());
        } catch (InvalidEnumerationValueException $e) {
            $isValid = false;
            $this->result->forProperty('visibility')->addError(
                new Error('Given value is not allowed!', 59)
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
            if ($value->getSettingVotingExpiresDate()->getTimestamp() < DateTimeUtility::today()->getTimestamp()) {
                $isValid = false;
                $this->result->forProperty('settingVotingExpiresDate')->addError(
                    new Error('The expiration date must be located in the future.', 61)
                );
            } elseif ($expiresAt = $value->getSettingVotingExpiresAt()) {
                if ($expiresAt->getTimestamp() < DateTimeUtility::now()->getTimestamp()) {
                    $isValid = false;
                    $this->result->forProperty('settingVotingExpiresTime')->addError(
                        new Error('Given time is in the past.', 63)
                    );
                }
            }

            if (!$value->getSettingVotingExpiresTime()) {
                $isValid = false;
                $this->result->forProperty('settingVotingExpiresTime')->addError(
                    new Error('Please also set a time.', 62)
                );
            }
        }
        return $isValid;
    }
}
