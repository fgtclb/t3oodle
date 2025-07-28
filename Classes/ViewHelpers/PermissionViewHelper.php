<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\ViewHelpers;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Domain\Permission\AccessDeniedException;
use FGTCLB\T3oodle\Domain\Permission\PollPermission;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * @see PollPermission
 */
final class PermissionViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('permissionClassName', 'string', '', false, PollPermission::class);
        $this->registerArgument('poll', BasePoll::class, 'Poll object', false, null);
        $this->registerArgument(
            'action',
            'string',
            'Name of action to ask for permissions, e.g. "voting" or "edit"',
            true
        );
        $this->registerArgument('negate', 'bool', 'Negates the result, when true', false, false);
    }

    /**
     * @param array{
     *     permissionClassName: class-string<PollPermission>,
     *     poll: BasePoll|null,
     *     action: string,
     *     negate: bool
     * } $arguments
     * @param RenderingContextInterface $renderingContext
     * @return bool
     * @throws AccessDeniedException
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext)
    {
        $permissionClass = $arguments['permissionClassName'];
        $currentUserIdent = UserIdentUtility::getCurrentUserIdent();
        $settings = $renderingContext->getVariableProvider()->get('settings');
        $poll = $arguments['poll'];
        /** @var PollPermission $permission */
        $permission = GeneralUtility::makeInstance(
            $permissionClass,
            $currentUserIdent,
            $settings
        );
        $status = $permission->isAllowed($poll, $arguments['action']);
        return $arguments['negate'] ? !$status : $status;
    }
}
