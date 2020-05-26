<?php declare(strict_types=1);
namespace T3\T3oodle\ViewHelpers\Math;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class AddViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument('subject', 'int', 'Subject');
        $this->registerArgument('number', 'int', 'Number to add to subject', true);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $subject = $arguments['subject'] ?? $renderChildrenClosure();
        return (int) $subject + (int) $arguments['number'];
    }
}
