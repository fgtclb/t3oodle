<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

final class EditPollEvent
{
    private readonly Poll $poll;
    private readonly array $settings;
    private readonly ViewInterface $view;
    private readonly object $caller;

    public function __construct(Poll $poll, array $settings, ViewInterface $view, object $caller)
    {
        $this->poll = $poll;
        $this->settings = $settings;
        $this->view = $view;
        $this->caller = $caller;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }

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
