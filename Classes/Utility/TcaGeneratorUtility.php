<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Utility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
final class TcaGeneratorUtility
{
    /**
     * Returns a ready to use array, for TCA (e.g for "items" property, in type select), based on defined constants.
     * @param string           $labelPrefix If empty, getHumanReadableName() is called, otherwise this label prefix (e.g.
     *                                      "LLL:EXT:../locallang.xlf:") is used together with the name of the constant.
     */
    public static function getItemListForEnumeration(
        string $enumeration,
        bool $showEmptyValue = false,
        int|string $emptyValue = 0,
        string $emptyLabel = '',
        string $labelPrefix = ''
    ): array {
        $items = [];
        if ($showEmptyValue) {
            $items[] = [$emptyLabel, $emptyValue];
        }
        foreach (call_user_func([$enumeration, 'getConstants']) as $value) {
            $label = empty($labelPrefix)
                        ? call_user_func([$enumeration, 'getHumanReadableName'], $value)
                        : $labelPrefix . call_user_func([$enumeration, 'getName'], $value);
            $items[] = [$label, $value];
        }

        return $items;
    }
}
