<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\ViewHelpers;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Permission\PollPermission;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * @see PollPermission
 */
class PermissionViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var PollPermission
     */
    private static $permission;

    public function initializeArguments(): void
    {
        $this->registerArgument('permissionClassName', 'string', '', false, PollPermission::class);
        $this->registerArgument('poll', 'object', 'Poll object', false);
        $this->registerArgument(
            'action',
            'string',
            'Name of action to ask for permissions, e.g. "voting" or "edit"',
            true
        );
        $this->registerArgument('negate', 'bool', 'Negates the result, when true', false, false);
    }

    private static function init(array $arguments, RenderingContextInterface $renderingContext): void
    {
        $currentUserIdent = UserIdentUtility::getCurrentUserIdent();
        self::$permission = GeneralUtility::makeInstance(
            $arguments['permissionClassName'],
            $currentUserIdent,
            $renderingContext->getVariableProvider()->get('settings')
        );
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): bool {
        self::init($arguments, $renderingContext);

        $poll = $arguments['poll'] ?? $renderChildrenClosure();

        $status = self::$permission->isAllowed($poll, $arguments['action']);
        if ($arguments['negate']) {
            return !$status;
        }

        return $status;
    }
}
