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
     * Returns a ready-to-use array for TCA (e.g. for the "items" property of a select field),
     * * based on the cases of a backed enum.
     *
     * @param string $labelPrefix If empty, the enum case name is converted to a human-readable label.
     *                                      Otherwise, this label prefix (e.g. "LLL:EXT:../locallang.xlf:")
     *                                      is used together with the enum case name.
     * @param class-string<\BackedEnum> $enumeration
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

        foreach ($enumeration::cases() as $case) {
            $value = $case->value;

            $label = $labelPrefix === '' || $labelPrefix === '0'
                ? ucwords(strtolower(str_replace('_', ' ', $case->name)))
                : $labelPrefix . $case->name;

            $items[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $items;
    }
}
