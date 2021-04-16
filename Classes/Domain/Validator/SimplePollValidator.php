<?php

declare(strict_types = 1);

namespace FGTCLB\T3oodle\Domain\Validator;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Enumeration\Visibility;
use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Utility\DateTimeUtility;
use FGTCLB\T3oodle\Utility\TranslateUtility;
use TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class SimplePollValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    protected $supportedOptions = [
        'action' => ['update', '"create" or "update"', 'string'],
    ];

    public function __construct(array $options = [], Result $result = null)
    {
        parent::__construct($options);
        if (null === $result) {
            throw new \InvalidArgumentException('SimplePollValidator requires result constructor argument, from parent validator');
        }
        $this->result = $result;
    }

    public function validate($value)
    {
        if (false === $this->acceptsEmptyValues || false === $this->isEmpty($value)) {
            $this->isValid($value);
        }

        return $this->result;
    }

    /**
     * @param BasePoll|null $value
     *
     * @return bool
     */
    protected function isValid($value)
    {
        if (!$value) {
            return true;
        }
        $statusOptions = $this->checkOptions($value);
        $statusInfo = $this->checkInfo($value);
        $statusAuthor = $this->checkAuthor($value);
        $statusSettings = $this->checkSettings($value);

        return $statusOptions && $statusInfo && $statusAuthor && $statusSettings;
    }

    protected function checkOptions(BasePoll $value): bool
    {
        $isValid = true;
        $optionsUnique = true;
        $optionValues = [];

        $options = $value->getOptions(true);

        // Check options count
        if (count($options) < 1) {
            $isValid = false;
            $this->result->forProperty('options')->addError(
                new Error(TranslateUtility::translate('validation.1592143000'), 1592143000)
            );
        }

        // Check unique options
        $i = 0;
        /** @var \FGTCLB\T3oodle\Domain\Model\Option $option */
        foreach ($options as $key => $option) {
            if ('create' === $this->options['action']) {
                $key = $i++;
            }
            if (in_array($option->getName(), $optionValues)) {
                $this->result->forProperty('options.' . $key . '.name')->addError(
                    new Error(TranslateUtility::translate('validation.1592143001', [$option->getName()]), 1592143001)
                );
                $optionsUnique = false;
            }
            $optionValues[] = $option->getName();
        }
        if (!$optionsUnique) {
            $isValid = false;
            $this->result->forProperty('options')->addError(
                new Error(TranslateUtility::translate('validation.1592143002'), 1592143002)
            );
        }

        return $isValid;
    }

    protected function checkInfo(BasePoll $value): bool
    {
        $isValid = true;
        if (empty(trim($value->getTitle()))) {
            $isValid = false;
            $this->result->forProperty('title')->addError(
                new Error(TranslateUtility::translate('validation.1592143006'), 1592143006)
            );
        }
        if (strlen($value->getTitle()) > 255) {
            $isValid = false;
            $this->result->forProperty('title')->addError(
                new Error(TranslateUtility::translate('validation.1592143007'), 1592143007)
            );
        }

        if ($value->getDescription() && strlen($value->getDescription()) > 65535) {
            $isValid = false;
            $this->result->forProperty('description')->addError(
                new Error(TranslateUtility::translate('validation.1592143008'), 1592143008)
            );
        }

        if ($value->getLink() && false === filter_var($value->getLink(), FILTER_VALIDATE_URL)) {
            $isValid = false;
            $this->result->forProperty('link')->addError(
                new Error(TranslateUtility::translate('validation.1592143009'), 1592143009)
            );
        }
        if ($value->getLink() && 0 !== strpos($value->getLink(), 'http')) {
            $isValid = false;
            $this->result->forProperty('link')->addError(
                new Error(TranslateUtility::translate('validation.1592143010'), 1592143010)
            );
        }
        try {
            new Visibility($value->getVisibility());
        } catch (InvalidEnumerationValueException $e) {
            $isValid = false;
            $this->result->forProperty('visibility')->addError(
                new Error(TranslateUtility::translate('validation.1592143011'), 1592143011)
            );
        }

        return $isValid;
    }

    protected function checkAuthor(BasePoll $value): bool
    {
        $isValid = true;
        if (!$value->getAuthor()) {
            if (empty(trim($value->getAuthorName()))) {
                $isValid = false;
                $this->result->forProperty('authorName')->addError(
                    new Error(TranslateUtility::translate('validation.1592143012'), 1592143012)
                );
            }
            if (empty(trim($value->getAuthorMail()))) {
                $isValid = false;
                $this->result->forProperty('authorMail')->addError(
                    new Error(TranslateUtility::translate('validation.1592143013'), 1592143013)
                );
            }
            if ($value->getAuthorMail() && !GeneralUtility::validEmail($value->getAuthorMail())) {
                $isValid = false;
                $this->result->forProperty('authorMail')->addError(
                    new Error(TranslateUtility::translate('validation.1592143014'), 1592143014)
                );
            }
        }

        return $isValid;
    }

    protected function checkSettings(BasePoll $value): bool
    {
        $isValid = true;
        if ($value->getSettingMaxVotesPerOption() < 0 || $value->getSettingMaxVotesPerParticipant() < 0) {
            $isValid = false;
            $this->result->forProperty('settingMaxVotesPerOption')->addError(
                new Error(TranslateUtility::translate('validation.1592143015'), 1592143015)
            );
        }
        if ($value->getSettingVotingExpiresDate()) {
            if ($value->getSettingVotingExpiresDate()->getTimestamp() < DateTimeUtility::today()->getTimestamp()) {
                $isValid = false;
                $this->result->forProperty('settingVotingExpiresDate')->addError(
                    new Error(TranslateUtility::translate('validation.1592143016'), 1592143016)
                );
            } elseif ($expiresAt = $value->getSettingVotingExpiresAt()) {
                if ($expiresAt->getTimestamp() < DateTimeUtility::now()->getTimestamp()) {
                    $isValid = false;
                    $this->result->forProperty('settingVotingExpiresTime')->addError(
                        new Error(TranslateUtility::translate('validation.1592143017'), 1592143017)
                    );
                }
            }
            if (!$value->getSettingVotingExpiresTime()) {
                $isValid = false;
                $this->result->forProperty('settingVotingExpiresTime')->addError(
                    new Error(TranslateUtility::translate('validation.1592143018'), 1592143018)
                );
            }
        }
        if ($value->isSettingSuperSecretMode() &&
            (!$value->isSettingSecretParticipants() || !$value->isSettingSecretVotings())
        ) {
            $isValid = false;
            $this->result->forProperty('settingSuperSecretMode')->addError(
                new Error(TranslateUtility::translate('validation.1599729001'), 1599729001)
            );
        }

        return $isValid;
    }
}
