<?php declare(strict_types=1);
namespace FGTCLB\T3oodle\ViewHelpers\Schedule;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\Option;
use FGTCLB\T3oodle\Utility\ScheduleOptionUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class SplitDayOptionsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument('get', 'string', '"dates" or "options" allowed', true);
        $this->registerArgument('options', 'array', 'Iterable with options', false);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $options = $arguments['options'] ?? $renderChildrenClosure();
        if (!$options || !is_iterable($options)) {
            throw new \InvalidArgumentException('Invalid options given!');
        }

        $items = [];
        /** @var Option|array $option */
        foreach ($options as $option) {
            $name = is_array($option) ? $option['name'] : $option->getName();
            $parts = GeneralUtility::trimExplode(ScheduleOptionUtility::DAY_OPTION_DELIMITER, $name, true, 2);
            if (count($parts) === 2) {
                if ($arguments['get'] === 'options') {
                    $items[] = $parts[1];
                } else {
                    $items[] = $parts[0];
                }
            } else {
                if ($arguments['get'] !== 'options') {
                    $items[] = $name;
                }
            }
        }
        return array_unique($items);
    }
}
