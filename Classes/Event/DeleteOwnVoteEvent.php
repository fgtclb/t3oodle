<?php
declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Vote;

class DeleteOwnVoteEvent {
    private Vote $vote;
    private string $participantName;
    private bool $continue;
    private array $settings;
    private PollController $caller;

    public function __construct(Vote $vote, string $participantName, bool $continue, array $settings, PollController $caller) {
        $this->vote = $vote;
        $this->participantName = $participantName;
        $this->continue = $continue;
        $this->settings = $settings;
        $this->caller = $caller;
    }

    public function getVote(): Vote {
        return $this->vote;
    }

    public function getParticipantName(): string {
        return $this->participantName;
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
