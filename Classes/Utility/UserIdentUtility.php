<?php

declare(strict_types = 1);

namespace FGTCLB\T3oodle\Utility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UserIdentUtility
{
    /**
     * @var string|null
     */
    private static $currentUserIdent;

    public static function getCurrentUserIdent(): ?string
    {
        if (self::$currentUserIdent) {
            return self::$currentUserIdent;
        }

        $userAspect = self::getCurrentUserAspect();
        if ($userAspect->isLoggedIn()) {
            self::$currentUserIdent = (string)$userAspect->get('id');
        } else {
            self::$currentUserIdent = CookieUtility::get('userIdent') ?? '';
        }

        return self::$currentUserIdent;
    }

    public static function generateNewUserIdent(): string
    {
        return base64_encode(uniqid('', true) . uniqid('', true));
    }

    public static function getCurrentUserAspect(): UserAspect
    {
        $context = GeneralUtility::makeInstance(Context::class);
        /** @var UserAspect $userAspect */
        $userAspect = $context->getAspect('frontend.user');

        return $userAspect;
    }
}
