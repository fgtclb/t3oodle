<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Traits\Model;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\PollFrontendUser as FrontendUser;
use FGTCLB\T3oodle\Utility\SettingsUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait DynamicUserProperties
{
    /**
     * @var array<array-key, mixed>
     */
    protected static $typoscriptSettings = [];

    /**
     * @var array<array-key, mixed>
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
            throw new \InvalidArgumentException(
                '$pluginSetting argument only allows values "name" or "mail".',
                1727787679
            );
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
        $userRow = $this->getUserRow((int)$user->getUid());

        $returnValue = $userRow[$fieldName] ?? null;

        if ($returnValue === null) {
            throw new \InvalidArgumentException(
                sprintf('Field "%s" not given.', $fieldName),
                1776774128
            );
        }

        return $returnValue;
    }

    /**
     * @return array<array-key, mixed>|null
     * @throws \Doctrine\DBAL\Exception
     */
    private function getUserRow(int $uid): ?array
    {
        if (array_key_exists($uid, self::$userRowCache)) {
            return self::$userRowCache[$uid];
        }

        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $pool->getConnectionForTable('fe_users');
        $queryBuilder = $connection->createQueryBuilder();
        $result = $queryBuilder
            ->select('uid', self::$typoscriptSettings['frontendUserNameField'])
            ->from('fe_users')
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
            ))
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if ($result === false) {
            return null;
        }

        self::$userRowCache[$uid] = $result;

        return self::$userRowCache[$uid];
    }
}
