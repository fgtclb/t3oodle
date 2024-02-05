<?php

declare(strict_types = 1);

namespace FGTCLB\T3oodle\Utility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Error;

class ScheduleOptionUtility
{
    public const DAY_OPTION_DELIMITER = ' - ';

    /**
     * Converts "2020-06-13 - 13:00-15:00" to.
     *
     * array
     *   original => '2020-06-13 - 13:00-15:00'
     *   day => '2020-06-13'
     *   option => '13:00-15:00'
     *   dateStart => DateTime (2020-06-13T13:00:00+02:00, 1592046000)
     *   dateEnd => DateTime (2020-06-13T15:00:00+02:00, 1592053200)
     */
    public static function parseOptionName(string $optionName): array
    {
        $parts = GeneralUtility::trimExplode(self::DAY_OPTION_DELIMITER, $optionName, true, 2);
        $result = [
            'original' => $optionName,
        ];

        $result['day'] = $parts[0];
        if (count($parts) > 1) {
            $result['option'] = $parts[1];
        }

        $result['dateStart'] = new \DateTime($result['day']);

        if (isset($result['option'])) {
            // Check if day option contains time(s)
            preg_match_all('/\d{1,2}:\d{2}/', $result['option'], $timeMatches);
            $timeMatches = current($timeMatches);
            if (count($timeMatches) > 0) {
                // set start time
                $timeParts = GeneralUtility::intExplode(':', $timeMatches[0], true);
                $result['dateStart']->setTime($timeParts[0], $timeParts[1]);
                if (count($timeMatches) > 1) {
                    $result['dateEnd'] = clone $result['dateStart'];
                    $timeParts = GeneralUtility::intExplode(':', $timeMatches[1], true);
                    $result['dateEnd']->setTime($timeParts[0], $timeParts[1]);
                }
            }
        }

        return $result;
    }

    public static function validateOptionName(string $optionName): array
    {
        $errors = [];
        $parts = GeneralUtility::trimExplode(' - ', $optionName, true, 2);
        if (count($parts) < 1) {
            $isValid = false;
            $errors[] = new Error(TranslateUtility::translate('validation.1592143003', [$optionName]), 1592143003);
        }
        if (isset($parts[0]) && $parts[0] && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $parts[0])) {
            $errors[] = new Error(TranslateUtility::translate('validation.1592143004', [$optionName]), 1592143004);
        }
        if (isset($parts[1]) && empty(trim($parts[1]))) {
            $errors[] = new Error(TranslateUtility::translate('validation.1592143005'), 1592143005);
        }

        return $errors;
    }
}
