<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3Fluid\Fluid\View\ViewInterface as FluidStandaloneViewInterface;

final readonly class ShowFinishEvent
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private Poll $poll,
        private array $settings,
        private FluidStandaloneViewInterface|ViewInterface $view,
        private PollController $caller,
    ) {}

    public function getPoll(): Poll
    {
        return $this->poll;
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
