<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Domain\Model\BasePoll;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3Fluid\Fluid\View\ViewInterface as FluidStandaloneViewInterface;

final readonly class ListPollEvent
{
    /**
     * @param QueryResultInterface<int, BasePoll> $polls
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private QueryResultInterface $polls,
        private array $settings,
        private FluidStandaloneViewInterface|ViewInterface $view,
        private object $caller,
    ) {}

    /**
     * @return QueryResultInterface<int, BasePoll>
     */
    public function getPolls(): QueryResultInterface
    {
        return $this->polls;
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

    public function getCaller(): object
    {
        return $this->caller;
    }
}
