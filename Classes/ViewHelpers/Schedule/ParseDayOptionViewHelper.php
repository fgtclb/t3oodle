<?php declare(strict_types=1);
namespace T3\T3oodle\ViewHelpers\Schedule;

use T3\T3oodle\Utility\ScheduleOptionUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ParseDayOptionViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'Date with option as string', false);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $value = $arguments['value'] ?? (string) $renderChildrenClosure();
        return ScheduleOptionUtility::parseOptionName($value);
    }
}
