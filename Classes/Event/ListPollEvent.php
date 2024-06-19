<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use TYPO3Fluid\Fluid\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

final class ListPollEvent
{
    private QueryResultInterface $polls;
    private array $settings;
    private ViewInterface $view;
    private object $caller;

    public function __construct(QueryResultInterface $polls, array $settings, ViewInterface $view, object $caller)
    {
        $this->polls = $polls;
        $this->settings = $settings;
        $this->view = $view;
        $this->caller = $caller;
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
