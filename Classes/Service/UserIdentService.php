<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Service;

use FGTCLB\T3oodle\Utility\CookieUtility;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service around retrieving current user ident.
 */
#[Autoconfigure(public: true)]
final class UserIdentService
{
    private const CURRENT_USER_IDENT_IDENTIFIER = 't3oodle-current-user-ident';

    public function __construct(
        #[Autowire(service: 'cache.runtime')]
        private readonly FrontendInterface $runtimeCache,
    ) {}

    public function getCurrentUserIdent(?Context $context = null): ?string
    {
        $currentUserIdent = $this->runtimeCache->get(self::CURRENT_USER_IDENT_IDENTIFIER);
        if ($currentUserIdent !== null) {
            return $currentUserIdent;
        }
        $userAspect = $this->getCurrentUserAspect($context);
        $currentUserIdent = $userAspect->isLoggedIn()
            ? (string)$userAspect->get('id')
            : CookieUtility::get('userIdent');
        if ($currentUserIdent !== null) {
            $this->runtimeCache->set(self::CURRENT_USER_IDENT_IDENTIFIER, $currentUserIdent);
        }
        return $currentUserIdent;
    }

    public function getCurrentUserAspect(?Context $context = null): UserAspect
    {
        $context ??= GeneralUtility::makeInstance(Context::class);
        /** @var UserAspect $userAspect */
        $userAspect = $context->getAspect('frontend.user');
        return $userAspect;
    }

    public function generateNewUserIdent(): string
    {
        return base64_encode(uniqid('', true) . uniqid('', true));
    }
}
