<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event\Permission;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll;

/**
 * This event manipulates the newOptions allowance check
 * @see PollController::showAction()
 */
final class NewOptionsAllowedEvent
{
    private BasePoll $poll;
    private bool $allowed;
    private PollController $controller;

    public function __construct(
        BasePoll $poll,
        bool $allowed,
        PollController $controller,
    ) {
        $this->poll = $poll;
        $this->allowed = $allowed;
        $this->controller = $controller;
    }

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
