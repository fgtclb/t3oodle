<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use TYPO3Fluid\Fluid\View\ViewInterface;

final class NewPollEvent
{
    /**
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $newOptions
     */
    public function __construct(
        private readonly Poll $poll,
        private readonly bool $publishDirectly,
        private readonly array $newOptions,
        private readonly array $settings,
        private readonly ViewInterface $view,
        private readonly PollController $caller,
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

    public function getView(): ViewInterface
    {
        return $this->view;
    }

    public function getCaller(): PollController
    {
        return $this->caller;
    }
}
