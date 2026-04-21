<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;

final class ResetVotesEvent
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private readonly Poll $poll,
        private bool $continue,
        private readonly array $settings,
        private readonly PollController $caller,
    ) {}

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function getContinue(): bool
    {
        return $this->continue;
    }

    /**
     * @return array<string, mixed>
     */
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
