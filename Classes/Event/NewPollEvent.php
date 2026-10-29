<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3Fluid\Fluid\View\ViewInterface as FluidStandaloneViewInterface;

final readonly class NewPollEvent
{
    /**
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $newOptions
     */
    public function __construct(
        private Poll $poll,
        private bool $publishDirectly,
        private array $newOptions,
        private array $settings,
        private FluidStandaloneViewInterface|ViewInterface $view,
        private PollController $caller,
    ) {}

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function getPublishDirectly(): bool
    {
        return $this->publishDirectly;
    }

    /**
     * @return array<string, mixed>
     */
    public function getNewOptions(): array
    {
        return $this->newOptions;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getView(): FluidStandaloneViewInterface|ViewInterface
    {
        return $this->view;
    }

    public function getCaller(): PollController
    {
        return $this->caller;
    }
}
