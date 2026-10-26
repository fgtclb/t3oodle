<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;

final readonly class CreateBeforeEvent
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private Poll $poll,
        private bool $publishDirectly,
        private bool $continue,
        private array $settings,
        private PollController $caller,
    ) {}

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function getPublishDirectly(): bool
    {
        return $this->publishDirectly;
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
}
