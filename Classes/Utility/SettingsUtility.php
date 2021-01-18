<?php declare(strict_types=1);
namespace FGTCLB\T3oodle\Utility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
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
