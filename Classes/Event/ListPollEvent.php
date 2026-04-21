<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

final class ListPollEvent
{
    private readonly QueryResultInterface $polls;
    private readonly ViewInterface $view;

    public function __construct(QueryResultInterface $polls, private readonly array $settings, ViewInterface $view, private readonly object $caller)
    {
        $this->polls = $polls;
        $this->view = $view;
    }

    public function getPolls(): QueryResultInterface
    {
        return $this->polls;
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
