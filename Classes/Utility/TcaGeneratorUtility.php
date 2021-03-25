<?php

declare(strict_types = 1);

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
     *
     * @param mixed|int|string $emptyValue
     * @param string           $labelPrefix If empty, getHumanReadableName() is called, otherwise this label prefix (e.g.
     *                                      "LLL:EXT:../locallang.xlf:") is used together with the name of the constant.
     */
    public static function getItemListForEnumeration(
        string $enumeration,
        bool $showEmptyValue = false,
        $emptyValue = 0,
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

    /**
     * Provides anonymous closure function to prefix locallang key.
     *
     * Usage in TCA:
     *
     * $ll = FGTCLB\T3oodle\Utility\TcaGeneratorUtility::getLocallangClosureFunction(
     *     'LLL:EXT:yourext/Resources/Private/Language/locallang_db.xlf:'
     * );
     *
     * ...
     *
     * [
     *     'label' => $ll('pages.tx_rooms_version'),
     *     'config' => [...]
     * ]
     */
    public static function getLocallangClosureFunction(string $prefix): \Closure
    {
        /*
         * Prepends given prefix to key.
         *
         * @param string $key
         * @return string Given key prepended with prefix
         */
        return static function (string $key) use ($prefix): string {
            return $prefix . $key;
        };
    }
}
