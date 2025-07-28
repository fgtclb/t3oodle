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
use FGTCLB\T3oodle\Service\UserService;
use FGTCLB\T3oodle\Utility\TranslateUtility;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PollPermission
{
    private string $currentUserIdent;

    /**
     * @var array
     */
    private array $controllerSettings;
    private UserService $userService;

    public function __construct(string $currentUserIdent = null, array $controllerSettings = [])
    {
        $this->currentUserIdent = $currentUserIdent ?? UserIdentUtility::getCurrentUserIdent();
        $this->controllerSettings = $controllerSettings;
        $this->userService = GeneralUtility::makeInstance(UserService::class);
    }

    /**
     * @throws AccessDeniedException
     */
    public function isAllowed(BasePoll|Vote|null $subject, string $action, bool $throwException = false): bool
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
        return $this->isViewingInGeneralAllowed($poll) || $this->userIsAuthor($poll);
    }

    public function isShowAllowed(BasePoll $poll): bool
    {
        return $poll->isPublished() || $this->userIsAuthor($poll);
    }

    public function isNewAllowed(): bool
    {
        return $this->isNewSimplePollAllowed() && $this->isNewSchedulePollAllowed();
    }

    public function isNewSimplePollAllowed(): bool
    {
        return (bool)$this->controllerSettings['allowNewSimplePolls'];
    }

    public function isNewSchedulePollAllowed(): bool
    {
        return (bool)$this->controllerSettings['allowNewSchedulePolls'];
    }

    public function isEditAllowed(BasePoll $poll): bool
    {
        return $this->userIsAuthor($poll) && !$poll->isFinished();
    }

    public function isDeleteAllowed(BasePoll $poll): bool
    {
        return $this->isEditAllowed($poll) && count($poll->getVotes()) === 0;
    }

    public function isPublishAllowed(BasePoll $poll): bool
    {
        return !$poll->isPublished() && !$poll->isFinished() && $this->userIsAuthor($poll);
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

        return $status;
    }

    public function isFinishSuggestionModeAllowed(BasePoll $poll): bool
    {
        return $this->isSuggestNewOptionsAllowed($poll) && $this->userIsAuthor($poll);
    }

    public function isSuggestNewOptionsAllowed(BasePoll $poll): bool
    {
        if (empty($this->controllerSettings)) {
            return false;
        }

        return (bool)$this->controllerSettings['allowSuggestionMode']
            && $poll->isPublished()
            && !$poll->isFinished()
            && $poll->isSuggestModeEnabled()
            && !$poll->isSuggestModeFinished();
    }

    public function isVotingAllowed(BasePoll $poll): bool
    {
        // TODO: Controller settings may stay empty, when called from models (like Poll->getStatus())
        return (empty($this->controllerSettings) || $this->controllerSettings['allowNewVotes'])
                && $poll->isPublished()
                && !$poll->isFinished()
                && !$this->isSuggestNewOptionsAllowed($poll)
                && !$poll->isVotingExpired()
                && (count($poll->getAvailableOptions()) > 0 || $poll->getHasCurrentUserVoted());
    }

    public function isSeeParticipantsDuringVotingAllowed(BasePoll $poll): bool
    {
        $status = false;
        if (!$poll->isSettingSuperSecretMode()) {
            $status = !$poll->isSettingSecretParticipants() || $this->userIsAuthor($poll);
        }

        return $status;
    }

    public function isSeeVotesDuringVotingAllowed(BasePoll $poll): bool
    {
        $status = false;
        if (!$poll->isSettingSuperSecretMode()) {
            $status = !$poll->isSettingSecretVotings() || $this->userIsAuthor($poll);
        }

        return $status;
    }

    public function isAdministrationAllowed(BasePoll $poll = null): bool
    {
        return $this->userService->userIsAdmin();
    }

    public function userIsAuthor(BasePoll $poll): bool
    {
        return $poll->getAuthorIdent() === $this->currentUserIdent || $this->userService->userIsAdmin();
    }

    public function isResetVotesAllowed(BasePoll $poll): bool
    {
        return $poll->isPublished()
                    && !$poll->isFinished()
                    && ($poll->getStatus()->equals(PollStatus::OPENED) || $poll->getStatus()->equals(PollStatus::CLOSED))
                    && $this->userIsAuthor($poll);
    }

    /**
     * @TODO should be located in a separate VotePermission class
     */
    public function isDeleteOwnVoteAllowed(Vote $vote): bool
    {
        return $this->controllerSettings['allowNewVotes']
            && !$vote->getPoll()->isFinished()
            && $vote->getPoll()->getStatus()->equals(PollStatus::OPENED)
            && $vote->getParticipantIdent() === $this->currentUserIdent;
    }
}
