<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use TYPO3Fluid\Fluid\View\ViewInterface;

final class NewPollEvent
{
    private Poll $poll;
    private bool $publishDirectly;
    private array $newOptions;
    private array $settings;
    private ViewInterface $view;
    private PollController $caller;

    public function __construct(Poll $poll, bool $publishDirectly, array $newOptions, array $settings, $view, PollController $caller)
    {
        $this->poll = $poll;
        $this->publishDirectly = $publishDirectly;
        $this->newOptions = $newOptions;
        $this->settings = $settings;
        $this->view = $view;
        $this->caller = $caller;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function getPublishDirectly(): bool
    {
        return $this->publishDirectly;
    }

    public function getNewOptions(): array
    {
        return $this->newOptions;
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
