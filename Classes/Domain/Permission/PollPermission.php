<?php declare(strict_types=1);
namespace FGTCLB\T3oodle\Domain\Permission;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

use FGTCLB\T3oodle\Domain\Enumeration\PollStatus;
use FGTCLB\T3oodle\Domain\Enumeration\Visibility;
use FGTCLB\T3oodle\Domain\Model\Poll;
use FGTCLB\T3oodle\Domain\Model\Vote;
use FGTCLB\T3oodle\Utility\SettingsUtility;
use FGTCLB\T3oodle\Utility\TranslateUtility;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class PollPermission
{
    /**
     * @var string
     */
    private $currentUserIdent;

    /**
     * @var array
     */
    private $controllerSettings;

    /**
     * @var Dispatcher
     */
    private $signalSlotDispatcher;

    public function __construct(string $currentUserIdent = null, array $controllerSettings = [])
    {
        if (!$currentUserIdent) {
            $currentUserIdent = UserIdentUtility::getCurrentUserIdent();
        }
        $this->currentUserIdent = $currentUserIdent;
        $this->controllerSettings = $controllerSettings;
        $this->signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
    }

    public function isAllowed($subject, string $action, bool $throwException = false): bool
    {
        $getter = 'is' . ucfirst($action) . 'Allowed';
        if (!method_exists($this, $getter)) {
            $available = [];
            foreach (get_class_methods(self::class) as $method) {
                $methodPart = substr($method, 2, -7);
                if (!empty($methodPart) && strpos($method, 'is') === 0) {
                    $available[] = lcfirst($methodPart);
                }
            }
            throw new \InvalidArgumentException(
                TranslateUtility::translate('exception.1592142419', [$action, implode(', ', $available)]),
                1592142419
            );
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
     * This is related to list view
     *
     * @param Poll $poll
     * @return bool
     */
    public function isViewingInGeneralAllowed(Poll $poll): bool
    {
        return $poll->getVisibility() !== Visibility::NOT_LISTED && $poll->isPublished();
    }

    /**
     * This is related to list view
     *
     * @param Poll $poll
     * @return bool
     */
    public function isViewingAllowed(Poll $poll): bool
    {
        $status = $this->isViewingInGeneralAllowed($poll) || $this->userIsAuthor($poll);
        $this->dispatch(__METHOD__, $status, $poll);
        return $status;
    }

    public function isShowAllowed(Poll $poll): bool
    {
        $status = $poll->isPublished() || $this->userIsAuthor($poll);
        return $this->dispatch(__METHOD__, $status, $poll);
    }

    public function isNewAllowed(): bool
    {
        $status = $this->isNewSimplePollAllowed() && $this->isNewSchedulePollAllowed();
        return $this->dispatch(__METHOD__, $status);
    }

    public function isNewSimplePollAllowed(): bool
    {
        $status = (bool) $this->controllerSettings['allowNewSimplePolls'];
        return $this->dispatch(__METHOD__, $status);
    }

    public function isNewSchedulePollAllowed(): bool
    {
        $status = (bool) $this->controllerSettings['allowNewSchedulePolls'];
        return $this->dispatch(__METHOD__, $status);
    }

    public function isEditAllowed(Poll $poll): bool
    {
        $status = $this->userIsAuthor($poll) && !$poll->isFinished();
        return $this->dispatch(__METHOD__, $status, $poll);
    }

    public function isDeleteAllowed(Poll $poll): bool
    {
        $status = $this->isEditAllowed($poll) && count($poll->getVotes()) === 0;
        return $this->dispatch(__METHOD__, $status, $poll);
    }

    public function isPublishAllowed(Poll $poll): bool
    {
        $status = !$poll->isPublished() && !$poll->isFinished() && $this->userIsAuthor($poll);
        return $this->dispatch(__METHOD__, $status, $poll);
    }

    public function isFinishAllowed(Poll $poll): bool
    {
        if ($poll->getSettingVotingExpiresAt() && !$poll->isVotingExpired()) {
            $status = false;
        } else {
            $status = $poll->isPublished() && !$poll->isFinished() && $this->userIsAuthor($poll);
        }
        return $this->dispatch(__METHOD__, $status, $poll);
    }

    public function isVotingAllowed(Poll $poll): bool
    {
        // TODO: Controller settings may stay empty, when called from models (like Poll->getStatus())
        $status = (empty($this->controllerSettings) || $this->controllerSettings['allowNewVotes'])
                && $poll->isPublished()
                && !$poll->isFinished()
                && !$poll->isVotingExpired()
                && (count($poll->getAvailableOptions()) > 0 || $poll->getHasCurrentUserVoted());
        return $this->dispatch(__METHOD__, $status, $poll);
    }

    public function isSeeParticipantsDuringVotingAllowed(Poll $poll): bool
    {
        $status = false;
        if (!$poll->isSettingSuperSecretMode()) {
            $status = !$poll->isSettingSecretParticipants() || $this->userIsAuthor($poll);
        }
        return $this->dispatch(__METHOD__, $status, $poll);
    }

    public function isSeeVotesDuringVotingAllowed(Poll $poll): bool
    {
        $status = false;
        if (!$poll->isSettingSuperSecretMode()) {
            $status = !$poll->isSettingSecretVotings() || $this->userIsAuthor($poll);
        }
        return $this->dispatch(__METHOD__, $status, $poll);
    }

    public function isAdministrationAllowed(Poll $poll = null): bool
    {
        $status = $this->userIsAdmin();
        if ($poll) {
            return $this->dispatch(__METHOD__, $status, $poll);
        }
        return $status;
    }

    private function userIsAuthor(Poll $poll): bool
    {
        $status = $poll->getAuthorIdent() === $this->currentUserIdent || $this->userIsAdmin();
        return $this->dispatch(__METHOD__, $status, $poll);
    }

    public function isResetVotesAllowed(Poll $poll): bool
    {
        $status = $poll->isPublished()
                    && !$poll->isFinished()
                    && $poll->getStatus()->equals(PollStatus::OPENED)
                    && $this->userIsAuthor($poll);
        return $this->dispatch(__METHOD__, $status, $poll);
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
        return $this->dispatch(__METHOD__, $status, $vote->getPoll(), $vote);
    }

    /**
     * @return bool
     * @TODO Move this to own class. This is not poll related
     */
    public function userIsAdmin(): bool
    {
        $currentUserIdent = UserIdentUtility::getCurrentUserIdent();
        if (is_numeric($currentUserIdent)) {
            $frontendUserUid = (int) $currentUserIdent;
            $settings = SettingsUtility::getTypoScriptSettings();
            if (!empty($settings['adminUserUids'])) {
                $adminUserUids = GeneralUtility::intExplode(',', $settings['adminUserUids'], true);
                if (in_array($frontendUserUid, $adminUserUids, true)) {
                    return true;
                }
            }
            if (!empty($settings['adminUserGroupUids'])) {
                $adminUserGroupUids = GeneralUtility::intExplode(',', $settings['adminUserGroupUids'], true);
                $userAspect = UserIdentUtility::getCurrentUserAspect();
                $setAdminGroups = array_intersect($userAspect->getGroupIds(), $adminUserGroupUids);
                if (count($setAdminGroups) > 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Dispatches slot in permissions signals.
     *
     * @param string $signalName
     * @param Poll $poll
     * @param bool $currentStatus
     * @param Vote|null $vote
     * @return bool
     */
    private function dispatch(string $signalName, bool $currentStatus, ?Poll $poll = null, ?Vote $vote = null): bool
    {
        $signalName = preg_replace('/(.*?::)(.*)/', '$2', $signalName);
        $arguments = [
            'currentStatus' => $currentStatus,
            'arguments' => [
                'controllerSettings' => $this->controllerSettings,
            ],
            'caller' => $this,
        ];
        if ($poll) {
            $arguments['arguments']['poll'] = $poll;
        }
        if ($vote) {
            $arguments['arguments']['vote'] = $vote;
        }

        $status = $this->signalSlotDispatcher->dispatch(__CLASS__, $signalName, $arguments);

        if (is_array($status) && array_key_exists('currentStatus', $status)) {
            return (bool) $status['currentStatus'];
        }
        return (bool) $status;
    }
}
