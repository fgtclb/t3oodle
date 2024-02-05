<?php
declare(strict_types=1);

namespace FGTCLB\T3oodle\Event\Permission;

use FGTCLB\T3oodle\Domain\Model\SchedulePoll;
use FGTCLB\T3oodle\Domain\Model\SimplePoll;
use FGTCLB\T3oodle\Domain\Model\Vote;

class PermissionCheckEvent {
    private bool $currentStatus;
    private ?array $arguments = [];
    private SchedulePoll|SimplePoll|Vote|null $caller;

    public function __construct(bool $currentStatus, ?array $arguments, $caller) {
        $this->currentStatus = $currentStatus;
        $this->arguments = $arguments;
        $this->caller = $caller;
    }

    public function getCurrentStatus(): bool {
        return $this->currentStatus;
    }

    public function setCurrentStatus(): void {
        $this->currentStatus;
    }

    public function getArguments(): array {
        return $this->arguments;
    }

    public function getCaller(): SchedulePoll|SimplePoll|Vote {
        return $this->caller;
    }
}
