<?php declare(strict_types=1);
namespace T3\T3oodle\Utility;

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
