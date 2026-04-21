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
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

class CustomPollValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    /**
     * @var array{
     *     action: non-empty-string[]
     * }
     */
    protected $supportedOptions = [
        'action' => ['update', '"create" or "update"', 'string'],
    ];

    /**
     * @param BasePoll|null $value
     */
    protected function isValid(mixed $value): void
    {
        if (!$value) {
            return;
        }

        $validatorFQCN = str_replace('\\Domain\\Model\\', '\\Domain\\Validator\\', $value::class) . 'Validator';
        if (!class_exists($validatorFQCN)) {
            return; // no validator, no validation
        }

        $validator = GeneralUtility::makeInstance($validatorFQCN, $this->getOptions(), $this->result);
        if ($validator instanceof ValidatorInterface) {
            $validator->validate($value);
        }
    }
}
