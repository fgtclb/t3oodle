<?php declare(strict_types=1);
namespace T3\T3oodle\ViewHelpers;

use T3\T3oodle\Domain\Model\Option;
use T3\T3oodle\Domain\Model\Vote;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class GetOptionValueViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument('vote', 'object', 'Vote object', false);
        $this->registerArgument('option', 'object', 'Option object', true);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        /** @var Vote $vote */
        $vote = $arguments['vote'] ?? $renderChildrenClosure();
        /** @var Option $option */
        $option = $arguments['option'];

        foreach ($vote->getOptionValues() as $key => $optionValue) {
            if ($optionValue->getOption() === $option) {
                return $optionValue;
            }
        }

        $optionValue = new \T3\T3oodle\Domain\Model\OptionValue();
        $optionValue->setOption($option);
        $optionValue->setValue('0');
        return $optionValue;
    }
}
