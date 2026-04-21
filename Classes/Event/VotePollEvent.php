<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Vote;

final class VotePollEvent
{
    public function __construct(private readonly Vote $vote, private readonly bool $isNew, private readonly array $settings, private bool $continue, private readonly PollController $caller) {}

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

    public function getSettings(): array
    {
        return $this->settings;
    }
}
