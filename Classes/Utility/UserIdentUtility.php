<?php declare(strict_types=1);
namespace T3\T3oodle\Utility;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UserIdentUtility
{
    private static $currentUserIdent;

    public static function getCurrentUserIdent(): ?string
    {
        if (self::$currentUserIdent) {
            return self::$currentUserIdent;
        }

        $userAspect = self::getCurrentUserAspect();
        if ($userAspect->isLoggedIn()) {
            self::$currentUserIdent = (string) $userAspect->get('id');
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
        return $context->getAspect('frontend.user');
    }
}
