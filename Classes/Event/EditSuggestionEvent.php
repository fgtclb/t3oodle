<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto;
use FGTCLB\T3oodle\Domain\Model\Option;
use TYPO3Fluid\Fluid\View\ViewInterface;

final class EditSuggestionEvent
{
    private Option $option;
    private SuggestionDto $suggestionDto;
    private ViewInterface $view;
    private array $settings;
    private PollController $caller;

    public function __construct(Option $option, SuggestionDto $suggestionDto, array $settings, ViewInterface $view, PollController $caller)
    {
        $this->option = $option;
        $this->suggestionDto = $suggestionDto;
        $this->view = $view;
        $this->settings = $settings;
        $this->caller = $caller;
    }

    public function getOption(): Option
    {
        return $this->option;
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
