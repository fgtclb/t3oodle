<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;

final class EditPollEvent
{
    private Poll $poll;
    private array $settings;
    private ViewInterface $view;
    private object $caller;

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
