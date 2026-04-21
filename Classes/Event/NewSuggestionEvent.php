<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto;
use TYPO3Fluid\Fluid\View\ViewInterface;

final class NewSuggestionEvent
{
    private readonly ViewInterface $view;

    public function __construct(private readonly Poll $poll, private readonly SuggestionDto $suggestionDto, private readonly array $settings, $view, private readonly PollController $caller)
    {
        $this->view = $view;
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
