<?php

declare(strict_types = 1);

namespace FGTCLB\T3oodle\Domain\Validator;

use FGTCLB\T3oodle\Utility\SettingsUtility;
use FGTCLB\T3oodle\Utility\TranslateUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class AcceptedTermsValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    protected function isValid($value)
    {
        $settings = SettingsUtility::getTypoScriptSettings();
        $isValid = true === $value || '0' === $settings['requireAcceptedTerms'];
        if (!$isValid) {
            $this->addError(TranslateUtility::translate('validation.1593008155', []), 1593008155);
        }

        return $isValid;
    }
}
