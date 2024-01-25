<?php
declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;

class CreateBeforeEvent {
    private Poll $poll;
    private bool $publishDirectly;
    private bool $continue;
    private array $settings;
    private PollController $caller;

    public function __construct(Poll $poll, bool $publishDirectly, bool $continue, array $settings, PollController $caller) {
        $this->poll = $poll;
        $this->publishDirectly = $publishDirectly;
        $this->continue = $continue;
        $this->settings = $settings;
        $this->caller = $caller;
    }

    public function getPoll(): Poll {
        return $this->poll;
    }

    public function getPublishDirectly(): bool {
        return $this->publishDirectly;
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
}
