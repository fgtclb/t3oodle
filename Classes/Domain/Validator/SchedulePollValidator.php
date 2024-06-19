<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Domain\Validator;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Utility\ScheduleOptionUtility;
use TYPO3\CMS\Extbase\Error\Result;

class SchedulePollValidator extends SimplePollValidator
{
    public function __construct(array $options = [], Result $result = null)
    {
        parent::__construct($options, $result);
        if ($result === null) {
            throw new \InvalidArgumentException('SchedulePollValidator requires result constructor argument, from parent validator');
        }
        $this->result = $result;
    }

    public function validate(mixed $value): Result
    {
        if ($this->acceptsEmptyValues === false || $this->isEmpty($value) === false) {
            $this->isValid($value);
        }

        return $this->result;
    }

    /**
     * @param BasePoll|null $value
     *
     * @return bool
     */
    protected function isValid($value): void
    {
        if (!$value) {
            return;
        }
        $isValid = $this->checkOptions($value)
                   && $this->checkInfo($value)
                   && $this->checkAuthor($value)
                   && $this->checkSettings($value);

        if (!$isValid) {
            $this->result->addError(
                new Error(TranslateUtility::translate('validation.generalError'), 1592143019)
            );
        }
    }

    protected function checkScheduleOptions(BasePoll $value): bool
    {
        $isValid = true;
        /** @var \FGTCLB\T3oodle\Domain\Model\Option[] $options */
        $options = $value->getOptions(true);
        foreach ($options as $key => $option) {
            $validationErrors = ScheduleOptionUtility::validateOptionName($option->getName());
            foreach ($validationErrors as $validationError) {
                $this->result->forProperty('options')->addError($validationError);
            }
        }

        return $isValid;
    }
}
