<?php declare(strict_types=1);
namespace T3\T3oodle\Utility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

final class TranslateUtility
{
    private const EXTENSION_NAME = 'T3oodle';

    public static function translate(string $key, array $arguments = [], string $default = ''): string
    {
        $translation = LocalizationUtility::translate($key, self::EXTENSION_NAME, $arguments);
        if (!$translation || empty($translation)) {
            return $default;
        }
        return $translation;
    }
}
