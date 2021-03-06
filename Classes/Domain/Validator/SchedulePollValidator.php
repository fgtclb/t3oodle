<?php

declare(strict_types = 1);

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
        if (null === $result) {
            throw new \InvalidArgumentException('SchedulePollValidator requires result constructor argument, from parent validator');
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
        $statusOptions = $this->checkOptions($value) && $this->checkScheduleOptions($value);
        $statusInfo = $this->checkInfo($value);
        $statusAuthor = $this->checkAuthor($value);
        $statusSettings = $this->checkSettings($value);

        return $statusOptions && $statusInfo && $statusAuthor && $statusSettings;
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
