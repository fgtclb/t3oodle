<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event\Permission;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto;

final class UpdateSuggestionAllowedEvent
{
    private BasePoll $poll;
    private bool $allowed;
    private PollController $controller;
    private ?SuggestionDto $suggestionDto;

    public function __construct(
        BasePoll $poll,
        bool $allowed,
        PollController $controller,
        ?SuggestionDto $suggestionDto
    ) {
        $this->poll = $poll;
        $this->allowed = $allowed;
        $this->controller = $controller;
        $this->suggestionDto = $suggestionDto;
    }

    public function getPoll(): BasePoll
    {
        return $this->poll;
    }

    public function getController(): PollController
    {
        return $this->controller;
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function setAllowed(bool $allowed): void
    {
        $this->allowed = $allowed;
    }

    public function getSuggestionDto(): ?SuggestionDto
    {
        return $this->suggestionDto;
    }
}
