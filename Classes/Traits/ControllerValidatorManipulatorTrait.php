<?php

declare(strict_types = 1);

namespace FGTCLB\T3oodle\Traits;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;

trait ControllerValidatorManipulatorTrait
{
    private function disableValidator(string $argument, string $validatorToRemove): void
    {
        if ($this->arguments->hasArgument($argument)) {
            /** @var ConjunctionValidator $argumentValidator */
            $argumentValidator = $this->arguments->getArgument($argument)->getValidator();
            $this->removeValidatorRecursively($argumentValidator, $validatorToRemove);
        }
    }

    private function removeValidatorRecursively(ConjunctionValidator $validator, string $validatorToRemove): void
    {
        $markToRemove = [];
        foreach ($validator->getValidators() as $subValidator) {
            if ($subValidator instanceof ConjunctionValidator) {
                $this->removeValidatorRecursively($subValidator, $validatorToRemove);
            }
            if ($subValidator instanceof $validatorToRemove) {
                $markToRemove[] = $subValidator;
            }
        }
        foreach ($markToRemove as $validatorMarkedToRemove) {
            $validator->removeValidator($validatorMarkedToRemove);
        }
    }

    private function disableGenericObjectValidator(string $argumentName, string $propertyName): void
    {
        if ($this->arguments->hasArgument($argumentName)) {
            $validator = $this->arguments->getArgument($argumentName)->getValidator();
            if (method_exists($validator, 'getValidators')) {
                foreach ($validator->getValidators() as $subValidator) {
                    if (method_exists($subValidator, 'getValidators')) {
                        foreach ($subValidator->getValidators() as $subValidatorSub) {
                            if (method_exists($subValidatorSub, 'getPropertyValidators')) {
                                $subValidatorSub->getPropertyValidators($propertyName)->removeAll(
                                    $subValidatorSub->getPropertyValidators($propertyName)
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}
