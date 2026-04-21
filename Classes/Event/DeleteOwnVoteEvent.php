<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Vote;

final class DeleteOwnVoteEvent
{
    private readonly Vote $vote;
    private readonly string $participantName;
    private bool $continue;
    private readonly array $settings;
    private readonly PollController $caller;

    public function __construct(Vote $vote, string $participantName, bool $continue, array $settings, PollController $caller)
    {
        $this->vote = $vote;
        $this->participantName = $participantName;
        $this->continue = $continue;
        $this->settings = $settings;
        $this->caller = $caller;
    }

    public function getVote(): Vote
    {
        return $this->vote;
    }

    public function getParticipantName(): string
    {
        return $this->participantName;
    }

    public function getContinue(): bool
    {
        return $this->continue;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getCaller(): PollController
    {
        return $this->caller;
    }

    public function setContinue(bool $continue): void
    {
        $this->continue = $continue;
    }
}
