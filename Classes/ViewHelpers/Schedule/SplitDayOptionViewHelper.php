<?php declare(strict_types=1);
namespace T3\T3oodle\ViewHelpers\Schedule;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class SplitDayOptionViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument('get', 'string', '"date" or "option" allowed', true);
        $this->registerArgument('value', 'string', 'Date with option as string', false);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $value = $arguments['value'] ?? (string) $renderChildrenClosure();
        $parts = GeneralUtility::trimExplode(' - ', $value, true, 2);

        if ($arguments['get'] === 'option') {
            return count($parts) === 2 ? $parts[1] : '';
        }
        return new \DateTime($parts[0]);
    }
}
