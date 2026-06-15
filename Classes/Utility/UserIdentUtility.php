<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Utility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

use FGTCLB\T3oodle\Service\UserIdentService;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @deprecated will be removed in next major version. Use {@see UserIdentService} instead.
 */
final class UserIdentUtility
{
    public static function getCurrentUserIdent(): ?string
    {
        trigger_error(
            sprintf(
                __METHOD__ . ' is deprecated, use "%s::%s" instead',
                UserIdentService::class, 'getCurrentUserIdent'
            ),
            E_USER_DEPRECATED,
        );
        return self::getUserIdentService()->getCurrentUserIdent();
    }

    public static function generateNewUserIdent(): string
    {
        trigger_error(
            sprintf(
                __METHOD__ . ' is deprecated, use "%s::%s" instead',
                UserIdentService::class, 'generateNewUserIdent'
            ),
            E_USER_DEPRECATED,
        );
        return self::getUserIdentService()->generateNewUserIdent();
    }

    public static function getCurrentUserAspect(): UserAspect
    {
        trigger_error(
            sprintf(
                __METHOD__ . ' is deprecated, use "%s::%s" instead',
                UserIdentService::class, 'getCurrentUserAspect'
            ),
            E_USER_DEPRECATED,
        );
        return self::getUserIdentService()->getCurrentUserAspect();
    }

    private static function getUserIdentService(): UserIdentService
    {
        return GeneralUtility::makeInstance(UserIdentService::class);
    }
}
