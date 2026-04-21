<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\ViewHelpers\Schedule;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\Option;
use FGTCLB\T3oodle\Utility\ScheduleOptionUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class SplitDayOptionsViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('get', 'string', '"dates" or "options" allowed', true);
        $this->registerArgument('options', 'array', 'Iterable with options', false);
    }

    /**
     * @return string[]
     */
    public function render(): array
    {
        $renderChildrenClosure = $this->buildRenderChildrenClosure();
        $options = $this->arguments['options'] ?? $renderChildrenClosure();
        if (!$options || !is_iterable($options)) {
            throw new \InvalidArgumentException(
                'Invalid options given!',
                1727787635
            );
        }
        $items = [];
        /** @var Option|array{name: string} $option */
        foreach ($options as $option) {
            $name = is_array($option) ? $option['name'] : $option->getName();
            $parts = GeneralUtility::trimExplode(ScheduleOptionUtility::DAY_OPTION_DELIMITER, $name, true, 2);
            if (count($parts) === 2) {
                $items[] = $this->arguments['get'] === 'options' ? $parts[1] : $parts[0];
            } elseif ($this->arguments['get'] !== 'options') {
                $items[] = $name;
            }
        }

        return array_unique($items);
    }
}
