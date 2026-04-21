<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event\PollRepository;

use FGTCLB\T3oodle\Domain\Repository\PollRepository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

final class FindPollsEvent
{
    private readonly QueryInterface $query;

    public function __construct(
        private array $constraints,
        QueryInterface $query,
        private readonly PollRepository $caller
    ) {
        $this->query = $query;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function getQuery(): QueryInterface
    {
        return $this->query;
    }
    public function setConstraints(array $constraints): void
    {
        $this->constraints = $constraints;
    }

    public function getCaller(): PollRepository
    {
        return $this->caller;
    }
}
