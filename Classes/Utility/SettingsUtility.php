<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Utility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

final class SettingsUtility
{
    /**
     * @var array<array-key, mixed>|null
     */
    private static ?array $settings = null;

    /**
     * @return array<array-key, mixed>
     * @throws InvalidConfigurationTypeException
     */
    public static function getTypoScriptSettings(): array
    {
        if (self::$settings) {
            return self::$settings;
        }
        $configManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $ts = $configManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        return self::$settings = $ts['plugin.']['tx_t3oodle.']['settings.'] ?? [];
    }
}
