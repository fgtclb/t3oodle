<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Service;

use FGTCLB\T3oodle\Utility\SettingsUtility;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

final class UserService
{
    /**
     * @throws InvalidConfigurationTypeException
     */
    public function userIsAdmin(): bool
    {
        $currentUserIdent = UserIdentUtility::getCurrentUserIdent();
        if (!is_numeric($currentUserIdent)) {
            return false;
        }

        $frontendUserUid = (int)$currentUserIdent;
        $settings = SettingsUtility::getTypoScriptSettings();

        return $this->isAdminByUid($frontendUserUid, $settings) || $this->isAdminByGroup($frontendUserUid, $settings);
    }

    /**
     * @param array<array-key, mixed> $settings
     */
    private function isAdminByUid(int $frontendUserUid, array $settings): bool
    {
        if (empty($settings['adminUserUids'])) {
            return false;
        }

        $adminUserUids = GeneralUtility::intExplode(',', $settings['adminUserUids'], true);

        return in_array($frontendUserUid, $adminUserUids, true);
    }

    /**
     * @param array<array-key, mixed> $settings
     */
    private function isAdminByGroup(int $frontendUserUid, array $settings): bool
    {
        if (empty($settings['adminUserGroupUids'])) {
            return false;
        }

        $adminUserGroupUids = GeneralUtility::intExplode(',', $settings['adminUserGroupUids'], true);
        $userAspect = UserIdentUtility::getCurrentUserAspect();
        $setAdminGroups = array_intersect($userAspect->getGroupIds(), $adminUserGroupUids);

        return count($setAdminGroups) > 0;
    }
}
