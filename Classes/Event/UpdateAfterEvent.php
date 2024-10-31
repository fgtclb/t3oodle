<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;

final class UpdateAfterEvent
{
    private Poll $poll;
    private int $voteCount;
    private bool $areOptionsModified;
    private bool $continue;
    private array $settings;
    private PollController $caller;

    public function __construct(Poll $poll, int $voteCount, bool $areOptionsModified, bool $continue, array $settings, PollController $caller)
    {
        $this->poll = $poll;
        $this->voteCount = $voteCount;
        $this->areOptionsModified = $areOptionsModified;
        $this->continue = $continue;
        $this->settings = $settings;
        $this->caller = $caller;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }
    public function getVoteCount(): int
    {
        return $this->voteCount;
    }
    public function getAreOptionsModified(): bool
    {
        return $this->areOptionsModified;
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
