<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Vote;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

final class ShowPollEvent
{
    private readonly ViewInterface $view;

    public function __construct(private mixed $poll, private Vote $vote, ViewInterface $view, private array $newOptionValues, private readonly array $settings, private readonly PollController $caller)
    {
        $this->view = $view;
    }

    public function getPoll(): mixed
    {
        return $this->poll;
    }

    public function setPoll(mixed $poll): void
    {
        $this->poll = $poll;
    }

    public function getView(): ViewInterface
    {
        return $this->view;
    }

    public function getCaller(): PollController
    {
        return $this->caller;
    }

    public function getVote(): Vote
    {
        return $this->vote;
    }

    public function setVote(Vote $vote): void
    {
        $this->vote = $vote;
    }

    public function getNewOptionValues(): array
    {
        return $this->newOptionValues;
    }

    public function setNewOptionValues(array $newOptionValues): void
    {
        $this->newOptionValues = $newOptionValues;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
