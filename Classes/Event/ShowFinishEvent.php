<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

final class ShowFinishEvent
{
    private readonly ViewInterface $view;

    public function __construct(private readonly Poll $poll, private readonly array $settings, ViewInterface $view, private readonly PollController $caller)
    {
        $this->view = $view;
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

    public function getCaller(): PollController
    {
        return $this->caller;
    }
}
