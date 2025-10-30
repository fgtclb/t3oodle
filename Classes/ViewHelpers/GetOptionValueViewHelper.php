<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\ViewHelpers;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\Option;
use FGTCLB\T3oodle\Domain\Model\OptionValue;
use FGTCLB\T3oodle\Domain\Model\Vote;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class GetOptionValueViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('option', Option::class, 'Option object', true);
        $this->registerArgument('vote', Vote::class, 'Vote object');
    }

    public function render(): OptionValue
    {
        $option = $this->arguments['option'];
        $renderChildrenClosure = $this->buildRenderChildrenClosure();
        /** @var Vote $vote */
        $vote = $this->arguments['vote'] ?? $renderChildrenClosure();
        foreach ($vote->getOptionValues() as $key => $optionValue) {
            if ($optionValue->getOption() === $option) {
                return $optionValue;
            }
        }

        $optionValue = new OptionValue();
        $optionValue->setOption($option);
        $optionValue->setValue('0');

        return $optionValue;
    }
}
