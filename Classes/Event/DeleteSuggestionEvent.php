<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Option;

final class DeleteSuggestionEvent
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private readonly Option $option,
        private bool $continue,
        private readonly array $settings,
        private readonly PollController $caller,
    ) {}

    public function getOption(): Option
    {
        return $this->option;
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
