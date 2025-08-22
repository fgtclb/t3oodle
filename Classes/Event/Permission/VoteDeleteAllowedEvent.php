<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event\Permission;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Vote;

final class VoteDeleteAllowedEvent
{
    private Vote $vote;
    private bool $allowed;
    private PollController $controller;

    public function __construct(
        Vote $vote,
        bool $allowed,
        PollController $controller,
    ) {
        $this->vote = $vote;
        $this->allowed = $allowed;
        $this->controller = $controller;
    }

    public function getVote(): Vote
    {
        return $this->vote;
    }

    public function getController(): PollController
    {
        return $this->controller;
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function setAllowed(bool $allowed): void
    {
        $this->allowed = $allowed;
    }
}
