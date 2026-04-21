<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\ViewHelpers\Schedule;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Utility\ScheduleOptionUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class ParseDayOptionViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'Date with option as string', false);
    }

    /**
     * @return array{
     *      original: string,
     *      day: string,
     *      option?: string,
     *      dateStart: \DateTimeInterface,
     *      dateEnd?: \DateTimeInterface
     *  }
     */
    public function render(): array
    {
        $renderChildrenClosure = $this->buildRenderChildrenClosure();
        return ScheduleOptionUtility::parseOptionName($this->arguments['value'] ?? $renderChildrenClosure());
    }
}
