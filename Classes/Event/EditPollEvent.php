<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use TYPO3Fluid\Fluid\View\ViewInterface;

final class EditPollEvent
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private readonly Poll $poll,
        private readonly array $settings,
        private readonly ViewInterface $view,
        private readonly object $caller,
    ) {}

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getView(): ViewInterface
    {
        return $this->view;
    }

    public function getCaller(): object
    {
        return $this->caller;
    }
}
