<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\ViewHelpers;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\Option;
use FGTCLB\T3oodle\Domain\Model\Vote;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class GetOptionValueViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
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

        $optionValue = new \FGTCLB\T3oodle\Domain\Model\OptionValue();
        $optionValue->setOption($option);
        $optionValue->setValue('0');

        return $optionValue;
    }
}
