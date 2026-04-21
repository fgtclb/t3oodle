<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Domain\Model\BasePoll;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3Fluid\Fluid\View\ViewInterface;

final class ListPollEvent
{
    /**
     * @param QueryResultInterface<BasePoll> $polls
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private readonly QueryResultInterface $polls,
        private readonly array $settings,
        private readonly ViewInterface $view,
        private readonly object $caller,
    ) {}

    /**
     * @return QueryResultInterface<BasePoll>
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

    public function getView(): ViewInterface
    {
        return $this->view;
    }

    public function getCaller(): object
    {
        return $this->caller;
    }
}
