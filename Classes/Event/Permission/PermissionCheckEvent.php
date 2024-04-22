<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event\Permission;

final class PermissionCheckEvent
{
    private bool $currentStatus;
    private ?array $arguments = [];
    private mixed $caller;

    public function __construct(bool $currentStatus, ?array $arguments, $caller)
    {
        $this->currentStatus = $currentStatus;
        $this->arguments = $arguments;
        $this->caller = $caller;
    }

    public function getCurrentStatus(): bool
    {
        return $this->currentStatus;
    }

    public function setCurrentStatus($status): void
    {
        $status ?? $this->currentStatus;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getCaller(): mixed
    {
        return $this->caller;
    }
}
