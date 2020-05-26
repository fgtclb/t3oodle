<?php declare(strict_types=1);
namespace T3\T3oodle\Domain\Permission;

use T3\T3oodle\Domain\Model\Poll;
use T3\T3oodle\Domain\Model\Vote;
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
            throw new \InvalidArgumentException(
                'Given action "' . $action . '" is not existing in ' . PollPermission::class
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
        return $this->userIsAuthor($poll);
    }

    public function isDeleteAllowed(Poll $poll): bool
    {
        return $this->isEditAllowed($poll) && !$poll->isPublished();
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
        return $poll->isPublished() && !$poll->isFinished();
    }

    /**
     * @TODO should be located in a seperate VotePermission class
     */
    public function isVoteDeletionAllowed(Vote $vote): bool
    {
        return $this->isVotingAllowed($vote->getParent()) && $this->userIsAuthor($vote->getParent());
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
