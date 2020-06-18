<?php declare(strict_types=1);
namespace T3\T3oodle\Utility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class LocalizationUtility extends \TYPO3\CMS\Extbase\Utility\LocalizationUtility
{
    /**
     * Modified section wrapped with comments
     * @see \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate()
     */
    public static function translate(
        $key,
        $extensionName = null,
        $arguments = null,
        string $languageKey = null,
        array $alternativeLanguageKeys = null
    ) {
        if ((string)$key === '') {
            // Early return guard: returns null if the key was empty, because the key may be a dynamic value
            // (from for example Fluid). Returning null allows null coalescing to a default value when that happens.
            return null;
        }
        $value = null;
        if (GeneralUtility::isFirstPartOfStr($key, 'LLL:')) {
            $keyParts = explode(':', $key);
            unset($keyParts[0]);
            $key = array_pop($keyParts);
            $languageFilePath = implode(':', $keyParts);
        } else {
            if (empty($extensionName)) {
                throw new \InvalidArgumentException(
                    'Parameter $extensionName cannot be empty if a fully-qualified key is not specified.',
                    1498144052
                );
            }
            $languageFilePath = static::getLanguageFilePath($extensionName);
        }
        $languageKeys = static::getLanguageKeys();
        if ($languageKey === null) {
            $languageKey = $languageKeys['languageKey'];
        }
        if (empty($alternativeLanguageKeys)) {
            $alternativeLanguageKeys = $languageKeys['alternativeLanguageKeys'];
        }

        static::initializeLocalization(
            $languageFilePath,
            $languageKey,
            $alternativeLanguageKeys,
            $extensionName
        );

        // TODO: START MODIFICATION
        $index = 0;
        if (is_array($arguments)) {
            $index = self::getIndexByQuantity((int) reset($arguments));
        }
        if (!isset(self::$LOCAL_LANG[$languageFilePath][$languageKey][$key][$index])) {
            $index = 0;
        }

        // The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
        if (!empty(self::$LOCAL_LANG[$languageFilePath][$languageKey][$key][$index]['target'])
            || isset(self::$LOCAL_LANG_UNSET[$languageFilePath][$languageKey][$key])
        ) {
            // Local language translation for key exists
            $value = self::$LOCAL_LANG[$languageFilePath][$languageKey][$key][$index]['target'];
        } elseif (!empty($alternativeLanguageKeys)) {
            $languages = array_reverse($alternativeLanguageKeys);
            foreach ($languages as $language) {
                if (!empty(self::$LOCAL_LANG[$languageFilePath][$language][$key][$index]['target'])
                    || isset(self::$LOCAL_LANG_UNSET[$languageFilePath][$language][$key])
                ) {
                    // Alternative language translation for key exists
                    $value = self::$LOCAL_LANG[$languageFilePath][$language][$key][$index]['target'];
                    break;
                }
            }
        }
        if ($value === null && (!empty(self::$LOCAL_LANG[$languageFilePath]['default'][$key][$index]['target'])
                || isset(self::$LOCAL_LANG_UNSET[$languageFilePath]['default'][$key]))
        ) {
            // Default language translation for key exists
            // No charset conversion because default is English and thereby ASCII
            $value = self::$LOCAL_LANG[$languageFilePath]['default'][$key][$index]['target'];
        }

        // TODO: END MODIFICATION

        if (is_array($arguments) && $value !== null) {
            // This unrolls arguments from $arguments - instead of calling vsprintf which receives arguments as an array
            // The reason is that only sprintf() will return an error message if the number of arguments does not match
            // the number of placeholders in the format string. Whereas, vsprintf would silently return nothing.
            return sprintf($value, ...array_values($arguments)) ?:
                sprintf(
                    'Error: could not translate key "%s" with value "%s" and %d argument(s)!',
                    $key,
                    $value,
                    count($arguments)
                );
        }
        return $value;
    }

    /**
     * Overwrites labels that are set via TypoScript.
     * TS locallang labels have to be configured like:
     * plugin.tx_myextension._LOCAL_LANG.languageKey.key = value
     *
     * With pluralization support!
     *
     * Example typoscript:
     * label.pollsFound._pluralize {
     *   0 = Nothing to see
     *   1 = Just one item found
     *   2 = %i items found
     * }
     *
     * @param string $extensionName
     * @param string $languageFilePath
     */
    protected static function loadTypoScriptLabels($extensionName, $languageFilePath)
    {
        $configurationManager = static::getConfigurationManager();
        $frameworkConfiguration = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            $extensionName
        );
        if (!is_array($frameworkConfiguration['_LOCAL_LANG'] ?? false)) {
            return;
        }
        self::$LOCAL_LANG_UNSET[$languageFilePath] = [];

        foreach ($frameworkConfiguration['_LOCAL_LANG'] as $languageKey => $labels) {
            if (!is_array($labels)) {
                continue;
            }
            foreach ($labels as $labelKey => $labelValue) {
                if (is_string($labelValue)) {
                    self::$LOCAL_LANG[$languageFilePath][$languageKey][$labelKey][0]['target'] = $labelValue;
                    if ($labelValue === '') {
                        self::$LOCAL_LANG_UNSET[$languageFilePath][$languageKey][$labelKey] = '';
                    }
                } elseif (is_array($labelValue)) {
                    $labelValue = self::flattenTypoScriptLabelArray($labelValue, $labelKey);
                    foreach ($labelValue as $key => $value) {
                        // TODO: START MODIFICATION
                        $keySplitted = GeneralUtility::trimExplode('.', $key);
                        $lastKeyPart = array_pop($keySplitted);
                        $secondLastKeyPart = array_pop($keySplitted);
                        if ($secondLastKeyPart === '_pluralize' && is_numeric($lastKeyPart)) {
                            $updatedKey = implode('.', $keySplitted);
                            self::$LOCAL_LANG[$languageFilePath][$languageKey][$updatedKey][$lastKeyPart]['target'] =
                                $value;
                        } else {
                            self::$LOCAL_LANG[$languageFilePath][$languageKey][$key][0]['target'] = $value;
                        }
                        // TODO: END MODIFICATION
                        if ($value === '') {
                            self::$LOCAL_LANG_UNSET[$languageFilePath][$languageKey][$key] = '';
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns pluralization form (XLIFF) by given quantity.
     * 0 -> 0
     * 1 -> 1
     * n -> 2
     *
     * @param int $quantity
     * @return int
     */
    private static function getIndexByQuantity(int $quantity): int
    {
        $index = $quantity;
        if ($index > 1) {
            $index = 2;
        }
        if ($index < 0) {
            $index = 0;
        }
        return $index;
    }
}
