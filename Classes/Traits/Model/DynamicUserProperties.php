<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Traits\Model;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Utility\SettingsUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

trait DynamicUserProperties
{
    /**
     * @var array
     */
    protected static $typoscriptSettings = [];

    /**
     * @var array
     */
    protected static $userRowCache = [];

    /**
     * @param string $pluginSetting "name" or "mail"
     */
    public function getPropertyDynamically(
        FrontendUser $user,
        string $pluginSetting = 'name',
        bool $showHintWhenEmpty = true
    ): string {
        if (!in_array($pluginSetting, ['name', 'mail'])) {
            throw new \InvalidArgumentException('$pluginSetting argument only allows values "name" or "mail".');
        }
        if (!self::$typoscriptSettings) {
            self::$typoscriptSettings = SettingsUtility::getTypoScriptSettings();
        }
        $fieldName = self::$typoscriptSettings['frontendUser' . ucfirst($pluginSetting) . 'Field'];
        $getter = $fieldName ?? 'name';
        $getter = 'get' . ucfirst($getter);
        if (method_exists($user, $getter)) {
            if ($showHintWhenEmpty) {
                return $user->$getter() ?: '<no ' . $pluginSetting . '>';
            }

            return $user->$getter() ?: '';
        }
        $userRow = $this->getUserRow($user->getUid());

        return $userRow[$fieldName];
    }

    private function getUserRow(int $uid): ?array
    {
        if (array_key_exists($uid, self::$userRowCache)) {
            return self::$userRowCache[$uid];
        }

        $pool = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);
        $connection = $pool->getConnectionForTable('fe_users');
        $queryBuilder = $connection->createQueryBuilder();
        self::$userRowCache[$uid] = $queryBuilder
            ->select('uid', self::$typoscriptSettings['frontendUserNameField'])
            ->from('fe_users')
            ->where($queryBuilder->expr()->eq('uid', $uid))
            ->execute()->fetch(\PDO::FETCH_ASSOC);

        return self::$userRowCache[$uid];
    }
}
