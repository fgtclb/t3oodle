<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;

final class FinishSuggestionModeEvent
{
    public function __construct(private readonly Poll $poll, private bool $continue, private readonly array $settings, private readonly PollController $caller)
    {
    }

    public function getPoll(): Poll
    {
        return $this->poll;
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
