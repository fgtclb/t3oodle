<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Service;

use FGTCLB\T3oodle\Utility\SettingsUtility;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

#[Autoconfigure(public: true)]
final class UserService
{
    public function __construct(
        private UserIdentService $userIdentService,
    ) {
    }

    /**
     * @throws InvalidConfigurationTypeException
     */
    public function userIsAdmin(): bool
    {
        $currentUserIdent = $this->userIdentService->getCurrentUserIdent();
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
        $userAspect = $this->userIdentService->getCurrentUserAspect();
        $setAdminGroups = array_intersect($userAspect->getGroupIds(), $adminUserGroupUids);

        return count($setAdminGroups) > 0;
    }
}
