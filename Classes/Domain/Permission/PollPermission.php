<?php declare(strict_types=1);
namespace T3\T3oodle\Domain\Permission;

use T3\T3oodle\Domain\Enumeration\Visibility;
use T3\T3oodle\Domain\Model\Poll;
use T3\T3oodle\Domain\Model\Vote;
use T3\T3oodle\Utility\SettingsUtility;
use T3\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class PollPermission
{
    private $currentUserIdent;

    public function __construct(string $currentUserIdent = null)
    {
        if (!$currentUserIdent) {
            $currentUserIdent = UserIdentUtility::getCurrentUserIdent();
        }
        $this->currentUserIdent = $currentUserIdent;
    }

    public function isAllowed(AbstractEntity $subject, string $action, bool $throwException = false): bool
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
                'Given action "' . $action . '" is not existing in ' . PollPermission::class . '. ' .
                'Available actions are: ' . implode(', ', $available)
            );
        }
        $result = $this->$getter($subject);

        if (!$result && $throwException) {
            throw new AccessDeniedException('Access denied for ' . $action . ' action.');
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
        return $this->isViewingInGeneralAllowed($poll) || $this->userIsAuthor($poll);
    }

    public function isShowAllowed(Poll $poll): bool
    {
        return $poll->isPublished() || $this->userIsAuthor($poll);
    }

    public function isEditAllowed(Poll $poll): bool
    {
        return $this->userIsAuthor($poll) && !$poll->isFinished();
    }

    public function isDeleteAllowed(Poll $poll): bool
    {
        return $this->isEditAllowed($poll) && count($poll->getVotes()) === 0;
    }

    public function isPublishAllowed(Poll $poll): bool
    {
        return !$poll->isPublished() && !$poll->isFinished() && $this->userIsAuthor($poll);
    }

    public function isFinishAllowed(Poll $poll): bool
    {
        return $poll->isPublished() && !$poll->isFinished() && $this->userIsAuthor($poll);
    }

    public function isVotingAllowed(Poll $poll): bool
    {
        // TODO: check if enough options are available (based on settingMaxVotesPerOption)
        return $poll->isPublished() && !$poll->isFinished() && !$poll->isVotingExpired();
    }

    /**
     * @TODO should be located in a seperate VotePermission class
     */
    public function isVoteDeletionAllowed(Vote $vote): bool
    {
        return $this->isVotingAllowed($vote->getParent()) &&
               ($this->userIsAuthor($vote->getParent()) || $vote->getParticipantIdent() === $this->currentUserIdent);
    }

    public function isSeeVotesDuringVotingAllowed(Poll $poll): bool
    {
        return !$poll->isSettingSecretVoting() || $this->userIsAuthor($poll);
    }

    public function isAdministrationAllowed(Poll $poll = null): bool
    {
        return $this->userIsAdmin();
    }

    private function userIsAuthor(Poll $poll): bool
    {
        return $poll->getAuthorIdent() === $this->currentUserIdent || $this->userIsAdmin();
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
}
