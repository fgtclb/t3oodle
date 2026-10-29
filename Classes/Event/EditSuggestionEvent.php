<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto;
use FGTCLB\T3oodle\Domain\Model\Option;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3Fluid\Fluid\View\ViewInterface as FluidStandaloneViewInterface;

final readonly class EditSuggestionEvent
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private Option $option,
        private SuggestionDto $suggestionDto,
        private array $settings,
        private FluidStandaloneViewInterface|ViewInterface $view,
        private PollController $caller,
    ) {}

    public function getOption(): Option
    {
        return $this->option;
    }
    public function getSuggestionDto(): SuggestionDto
    {
        return $this->suggestionDto;
    }

    public function getView(): FluidStandaloneViewInterface|ViewInterface
    {
        return $this->view;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getCaller(): PollController
    {
        return $this->caller;
    }
}
