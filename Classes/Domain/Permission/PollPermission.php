<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Domain\Permission;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

use FGTCLB\T3oodle\Domain\Enumeration\PollStatus;
use FGTCLB\T3oodle\Domain\Enumeration\Visibility;
use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Domain\Model\Vote;
use FGTCLB\T3oodle\Event\Permission\PermissionCheckEvent;
use FGTCLB\T3oodle\Service\UserService;
use FGTCLB\T3oodle\Utility\TranslateUtility;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PollPermission
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    private $currentUserIdent;

    /**
     * @var array
     */
    private $controllerSettings;

    private UserService $userService;

    public function __construct(string $currentUserIdent = null, array $controllerSettings = [])
    {
        if (!$currentUserIdent) {
            $currentUserIdent = UserIdentUtility::getCurrentUserIdent();
        }
        $this->currentUserIdent = $currentUserIdent;
        $this->controllerSettings = $controllerSettings;
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $this->userService = GeneralUtility::makeInstance(UserService::class);
    }

    /**
     * @param mixed $subject Is almost always Poll, but can also be Vote (see self::isDeleteOwnVoteAllowed)
     *
     * @throws AccessDeniedException
     */
    public function isAllowed($subject, string $action, bool $throwException = false): bool
    {
        $getter = 'is' . ucfirst($action) . 'Allowed';
        if (!method_exists($this, $getter)) {
            $available = [];
            foreach (get_class_methods(self::class) as $method) {
                $methodPart = substr($method, 2, -7);
                if (!empty($methodPart) &&   str_starts_with($method, 'is')) {
                    $available[] = lcfirst($methodPart);
                }
            }
            throw new \InvalidArgumentException(TranslateUtility::translate('exception.1592142419', [$action, implode(', ', $available)]), 1592142419);
        }
        $result = $this->$getter($subject);

        if (!$result && $throwException) {
            $customErrorMessage = TranslateUtility::translate('exception.permission.' . $action);
            if (empty($customErrorMessage)) {
                $customErrorMessage = TranslateUtility::translate('exception.1592142348', [$action]);
            }
            throw new AccessDeniedException($customErrorMessage, 1592142348);
        }

        return $result;
    }

    /**
     * This is related to list view.
     */
    public function isViewingInGeneralAllowed(BasePoll $poll): bool
    {
        return $poll->getVisibility() !== Visibility::NOT_LISTED && $poll->isPublished();
    }

    /**
     * This is related to list view.
     */
    public function isViewingAllowed(BasePoll $poll): bool
    {
        $status = $this->isViewingInGeneralAllowed($poll) || $this->userIsAuthor($poll);
        $this->dispatch($status, $poll);

        return $status;
    }

    public function isShowAllowed(BasePoll $poll): bool
    {
        $status = $poll->isPublished() || $this->userIsAuthor($poll);
        return $this->dispatch($status, $poll);
    }

    public function isNewAllowed(): bool
    {
        $status = $this->isNewSimplePollAllowed() && $this->isNewSchedulePollAllowed();

        return $this->dispatch($status);
    }

    public function isNewSimplePollAllowed(): bool
    {
        $status = (bool)$this->controllerSettings['allowNewSimplePolls'];

        return $this->dispatch($status);
    }

    public function isNewSchedulePollAllowed(): bool
    {
        $status = (bool)$this->controllerSettings['allowNewSchedulePolls'];

        return $this->dispatch($status);
    }

    public function isEditAllowed(BasePoll $poll): bool
    {
        $status = $this->userIsAuthor($poll) && !$poll->isFinished();

        return $this->dispatch($status, $poll);
    }

    public function isDeleteAllowed(BasePoll $poll): bool
    {
        $status = $this->isEditAllowed($poll) && count($poll->getVotes()) === 0;

        return $this->dispatch($status, $poll);
    }

    public function isPublishAllowed(BasePoll $poll): bool
    {
        $status = !$poll->isPublished() && !$poll->isFinished() && $this->userIsAuthor($poll);

        return $this->dispatch($status, $poll);
    }

    public function isFinishAllowed(BasePoll $poll): bool
    {
        if ($poll->getSettingVotingExpiresAt() && !$poll->isVotingExpired()) {
            $status = false;
        } else {
            $status = $poll->isPublished()
                && !$poll->isFinished()
                && !$this->isSuggestNewOptionsAllowed($poll)
                && $this->userIsAuthor($poll);
        }

        return $this->dispatch($status, $poll);
    }

    public function isFinishSuggestionModeAllowed(BasePoll $poll): bool
    {
        $status = $this->isSuggestNewOptionsAllowed($poll) && $this->userIsAuthor($poll);

        return $this->dispatch($status, $poll);
    }

    public function isSuggestNewOptionsAllowed(BasePoll $poll): bool
    {
        if (empty($this->controllerSettings)) {
            return false;
        }

        $status = (bool)$this->controllerSettings['allowSuggestionMode']
            && $poll->isPublished()
            && !$poll->isFinished()
            && $poll->isSuggestModeEnabled()
            && !$poll->isSuggestModeFinished();

        return $this->dispatch($status, $poll);
    }

    public function isVotingAllowed(BasePoll $poll): bool
    {
        // TODO: Controller settings may stay empty, when called from models (like Poll->getStatus())
        $status = (empty($this->controllerSettings) || $this->controllerSettings['allowNewVotes'])
                && $poll->isPublished()
                && !$poll->isFinished()
                && !$this->isSuggestNewOptionsAllowed($poll)
                && !$poll->isVotingExpired()
                && (count($poll->getAvailableOptions()) > 0 || $poll->getHasCurrentUserVoted());

        return $this->dispatch($status, $poll);
    }

    public function isSeeParticipantsDuringVotingAllowed(BasePoll $poll): bool
    {
        $status = false;
        if (!$poll->isSettingSuperSecretMode()) {
            $status = !$poll->isSettingSecretParticipants() || $this->userIsAuthor($poll);
        }

        return $this->dispatch($status, $poll);
    }

    public function isSeeVotesDuringVotingAllowed(BasePoll $poll): bool
    {
        $status = false;
        if (!$poll->isSettingSuperSecretMode()) {
            $status = !$poll->isSettingSecretVotings() || $this->userIsAuthor($poll);
        }

        return $this->dispatch($status, $poll);
    }

    public function isAdministrationAllowed(BasePoll $poll = null): bool
    {
        $status = $this->userService->userIsAdmin();
        if ($poll) {
            return $this->dispatch($status, $poll);
        }

        return $status;
    }

    private function userIsAuthor(BasePoll $poll): bool
    {
        $status = $poll->getAuthorIdent() === $this->currentUserIdent || $this->userService->userIsAdmin();

        return $this->dispatch($status, $poll);
    }

    public function isResetVotesAllowed(BasePoll $poll): bool
    {
        $status = $poll->isPublished()
                    && !$poll->isFinished()
                    && ($poll->getStatus()->equals(PollStatus::OPENED) || $poll->getStatus()->equals(PollStatus::CLOSED))
                    && $this->userIsAuthor($poll);

        return $this->dispatch($status, $poll);
    }

    /**
     * @TODO should be located in a separate VotePermission class
     */
    public function isDeleteOwnVoteAllowed(Vote $vote): bool
    {
        $status = $this->controllerSettings['allowNewVotes']
            && !$vote->getPoll()->isFinished()
            && $vote->getPoll()->getStatus()->equals(PollStatus::OPENED)
            && $vote->getParticipantIdent() === $this->currentUserIdent;

        return $this->dispatch($status, $vote);
    }

    /**
     * Dispatches slot in permissions signals.
     *
     * @param  BasePoll|Vote|null $caller
     */
    private function dispatch(bool $currentStatus, mixed $caller = null): bool
    {
        $arguments = [
            'currentStatus' => $currentStatus,
            'arguments' => [
                'controllerSettings' => $this->controllerSettings,
            ],
            'caller' => $this,
        ];
        if ($caller instanceof BasePoll) {
            $arguments['arguments']['poll'] = $caller;
        }
        if ($caller instanceof Vote) {
            $arguments['arguments']['vote'] = $caller;
        }

        $event = new PermissionCheckEvent($arguments['currentStatus'], $arguments, $caller);
        $status = $this->eventDispatcher->dispatch($event)->getCurrentStatus();

        if ($status) {
            return (bool)$status;
        }

        return (bool)$status;
    }
}
