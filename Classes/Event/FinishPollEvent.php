<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use FGTCLB\T3oodle\Domain\Model\Option;
use TYPO3Fluid\Fluid\View\ViewInterface;

final class FinishPollEvent
{
    private Poll $poll;
    private Option $finalOption;
    private bool $continue;
    private array $settings;
    private ViewInterface $view;
    private PollController $caller;

    public function __construct(Poll $poll, Option $finalOption, bool $continue, array $settings, ViewInterface $view, PollController $caller)
    {
        $this->poll = $poll;
        $this->finalOption = $finalOption;
        $this->continue = $continue;
        $this->settings = $settings;
        $this->view = $view;
        $this->caller = $caller;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function getFinalOption(): Option
    {
        return $this->finalOption;
    }

    public function getContinue(): bool
    {
        return $this->continue;
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

    public function setContinue(bool $continue): void
    {
        $this->continue = $continue;
    }
}
