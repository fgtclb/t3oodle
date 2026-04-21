<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto;
use TYPO3Fluid\Fluid\View\ViewInterface;

final class NewSuggestionEvent
{
    private readonly Poll $poll;
    private readonly array $settings;
    private readonly ViewInterface $view;
    private readonly PollController $caller;
    private readonly SuggestionDto $suggestionDto;

    public function __construct(Poll $poll, SuggestionDto $suggestionDto, array $settings, $view, PollController $caller)
    {
        $this->poll = $poll;
        $this->suggestionDto = $suggestionDto;
        $this->settings = $settings;
        $this->view = $view;
        $this->caller = $caller;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }
    public function getSuggestionDto(): SuggestionDto
    {
        return $this->suggestionDto;
    }

    public function getView(): ViewInterface
    {
        return $this->view;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getCaller(): PollController
    {
        return $this->caller;
    }

}
