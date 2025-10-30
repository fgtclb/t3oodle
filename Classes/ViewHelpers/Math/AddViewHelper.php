<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\ViewHelpers\Math;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper for simple adding two numbers together
 *
 * @deprecated No longer needed, as fluid itself could handle simple maths
 */
final class AddViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('subject', 'int', 'Subject');
        $this->registerArgument('number', 'int', 'Number to add to subject', true);
    }

    public function render(): int
    {
        $renderChildrenClosure = $this->buildRenderChildrenClosure();
        $subject = (int)($this->arguments['subject'] ?? $renderChildrenClosure());
        $number = (int)($this->arguments['number']);
        return $subject + $number;
    }
}
