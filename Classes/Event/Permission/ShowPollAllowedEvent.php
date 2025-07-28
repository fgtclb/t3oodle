<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event\Permission;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll;

/**
 * This event allows manipulating the Permission check in the PollController show
 * action. This is a replacement for the generic PermissionCheckEvent
 * @see PollController::showAction()
 */
final class ShowPollAllowedEvent
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
