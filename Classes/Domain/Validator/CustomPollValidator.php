<?php

declare(strict_types = 1);

namespace FGTCLB\T3oodle\Domain\Validator;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\BasePoll;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class CustomPollValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    protected $supportedOptions = [
        'action' => ['update', '"create" or "update"', 'string'],
    ];

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

        $validatorFQCN = str_replace('\\Domain\\Model\\', '\\Domain\\Validator\\', get_class($value)) . 'Validator';
        if (!class_exists($validatorFQCN)) {
            return false; // no validator, no validation
        }

        $validator = GeneralUtility::makeInstance($validatorFQCN, $this->getOptions(), $this->result);
        $validator->validate($value);

        return !$this->result->hasMessages();
    }
}
