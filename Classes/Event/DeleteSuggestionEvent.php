<?php
declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Option;

class DeleteSuggestionEvent {
    private Option $option;
    private bool $continue;
    private array $settings;
    private PollController $caller;

    public function __construct(Option $option, bool $continue, array $settings, PollController $caller) {
        $this->option = $option;
        $this->continue = $continue;
        $this->settings = $settings;
        $this->caller = $caller;
    }

    public function getOption(): Option {
        return $this->option;
    }

    public function getContinue(): bool {
        return $this->continue;
    }

    public function getSettings(): array {
        return $this->settings;
    }

    public function getCaller(): PollController {
        return $this->caller;
    }

    public function setContinue(bool $continue): void {
        $this->continue = $continue;
    }
}
