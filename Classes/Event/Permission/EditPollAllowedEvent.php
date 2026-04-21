<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event\Permission;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll;

final class EditPollAllowedEvent
{
    public function __construct(private readonly BasePoll $poll, private bool $allowed, private readonly PollController $controller) {}

    public function getPoll(): BasePoll
    {
        return $this->poll;
    }

    public function getController(): PollController
    {
        return $this->controller;
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function setAllowed(bool $allowed): void
    {
        $this->allowed = $allowed;
    }
}
