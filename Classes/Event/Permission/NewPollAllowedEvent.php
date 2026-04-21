<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event\Permission;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Domain\Permission\AccessDeniedException;

/**
 * This event adds the possibility of changing the allowing to create a new poll early.
 * This won't change the behaviour and will, in case of being false, throw an Exception.
 *
 * @see PollController::newAction()
 * @see AccessDeniedException
 */
final class NewPollAllowedEvent
{
    public function __construct(private readonly null|BasePoll $poll, private bool $allowed)
    {
    }

    public function getPoll(): ?BasePoll
    {
        return $this->poll;
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
