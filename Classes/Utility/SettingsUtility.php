<?php declare(strict_types=1);
namespace T3\T3oodle\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class SettingsUtility
{
    private static $settings;

    public static function getTypoScriptSettings()
    {
        if (self::$settings) {
            return self::$settings;
        }
        $configManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $ts = $configManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        return self::$settings = $ts['plugin.']['tx_t3oodle.']['settings.'] ?? [];
    }
}
