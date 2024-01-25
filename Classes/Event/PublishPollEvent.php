<?php
declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;

class PublishPollEvent {
    private Poll $poll;
    private bool $continue;
    private array $settings;
    private PollController $caller;

    public function __construct(Poll $poll, bool $continue, array $settings, PollController $caller) {
        $this->poll = $poll;
        $this->continue = $continue;
        $this->settings = $settings;
        $this->caller = $caller;
    }

    public function getPoll(): Poll {
        return $this->poll;
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
