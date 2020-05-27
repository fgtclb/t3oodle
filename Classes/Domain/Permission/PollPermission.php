<?php declare(strict_types=1);
namespace T3\T3oodle\Domain\Permission;

use T3\T3oodle\Domain\Model\Poll;
use T3\T3oodle\Domain\Model\Vote;
use T3\T3oodle\Utility\DateTimeUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class PollPermission
{
    private $currentUserIdent;

    public function __construct(string $currentUserIdent = null)
    {
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
        return $poll->isPublished() && !$poll->isFinished() && !$poll->isVotingExpired();
    }

    /**
     * @TODO should be located in a seperate VotePermission class
     */
    public function isVoteDeletionAllowed(Vote $vote): bool
    {
        return $this->isVotingAllowed($vote->getParent()) && $this->userIsAuthor($vote->getParent());
    }

    public function isSeeVotesDuringVotingAllowed(Poll $poll): bool
    {
        return !$poll->isSettingAnonymousVoting() || $this->userIsAuthor($poll);
    }

    private function userIsAuthor(Poll $poll): bool
    {
        return $poll->getAuthorIdent() === $this->currentUserIdent || $this->userIsAdmin();
    }

    private function userIsAdmin(): bool
    {
        return false; // TODO: implement me, this should be configurable
    }
}
