<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Traits;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

trait ControllerValidatorManipulatorTrait
{
    /**
     * @param class-string<ValidatorInterface> $validatorToRemove
     * @throws NoSuchValidatorException
     * @throws NoSuchArgumentException
     */
    private function disableValidator(string $argument, string $validatorToRemove): void
    {
        if ($this->arguments->hasArgument($argument)) {
            /** @var ConjunctionValidator $argumentValidator */
            $argumentValidator = $this->arguments->getArgument($argument)->getValidator();
            $this->removeValidatorRecursively($argumentValidator, $validatorToRemove);
        }
    }

    /**
     * @param class-string<ValidatorInterface> $validatorToRemove
     * @throws NoSuchValidatorException
     */
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

            if (!$validator instanceof ValidatorInterface) {
                GeneralUtility::makeInstance(LogManager::class)
                    ->getLogger(self::class)->warning("No validator found for argument $argumentName");
                return;
            }

            if (method_exists($validator, 'getValidators')) {
                foreach ($validator->getValidators() as $subValidator) {
                    if ($subValidator instanceof ValidatorInterface && method_exists($subValidator, 'getValidators')) {
                        foreach ($subValidator->getValidators() as $subValidatorSub) {
                            if ($subValidatorSub instanceof ValidatorInterface && method_exists($subValidatorSub, 'getPropertyValidators')) {
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
