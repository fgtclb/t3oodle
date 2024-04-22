<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Vote;

final class VotePollEvent
{
    private Vote $vote;
    private bool $isNew;
    private array $settings;
    private bool $continue;
    private PollController $caller;

    public function __construct(Vote $vote, bool $isNew, array $settings, bool $continue, PollController $caller)
    {
        $this->vote = $vote;
        $this->isNew = $isNew;
        $this->settings = $settings;
        $this->continue = $continue;
        $this->caller = $caller;
    }

    public function getVote(): Vote
    {
        return $this->vote;
    }

    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    public function shouldContinue(): bool
    {
        return $this->continue;
    }

    public function setContinue(bool $continue): void
    {
        $this->continue = $continue;
    }
    public function getCaller(): PollController
    {
        return $this->caller;
    }
}
