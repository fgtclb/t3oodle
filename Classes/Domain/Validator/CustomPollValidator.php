<?php

declare(strict_types=1);

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
     */
    protected function isValid($value): void
    {
        if (!$value) {
            return;
        }

        $validatorFQCN = str_replace('\\Domain\\Model\\', '\\Domain\\Validator\\', get_class($value)) . 'Validator';
        if (!class_exists($validatorFQCN)) {
            $this->addError('No validator found.', 1623948293);
            return; // no validator, no validation
        }

        $validator = GeneralUtility::makeInstance($validatorFQCN, $this->getOptions(), $this->result);
        $validator->validate($value);

        if ($this->result->hasMessages()) {
            $this->addError('Validation errors found.', 1623948294);
        }
    }
}
