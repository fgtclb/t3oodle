<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use FGTCLB\T3oodle\Domain\Model\Option;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

final class FinishPollEvent
{
    private readonly ViewInterface $view;

    public function __construct(private readonly Poll $poll, private readonly Option $finalOption, private bool $continue, private readonly array $settings, ViewInterface $view, private readonly PollController $caller)
    {
        $this->view = $view;
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
