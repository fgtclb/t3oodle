<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\SimplePoll;
use FGTCLB\T3oodle\Domain\Model\Vote;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

final class ShowPollEvent
{
    private SimplePoll $poll;

    private Vote $vote;

    private array $newOptionValues = [];
    private array $settings = [];
    private ViewInterface $view;

    private PollController $caller;

    public function __construct(SimplePoll $poll, Vote $vote, ViewInterface $view, array $newOptionValues, array $settings, PollController $caller)
    {
        $this->poll = $poll;
        $this->vote = $vote;
        $this->view = $view;
        $this->newOptionValues = $newOptionValues;
        $this->settings = $settings;
        $this->caller = $caller;
    }

    public function getPoll(): SimplePoll
    {
        return $this->poll;
    }

    public function setPoll(SimplePoll $poll): void
    {
        $this->poll = $poll;
    }

    public function getView(): ViewInterface
    {
        return $this->view;
    }

    public function getCaller()
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
