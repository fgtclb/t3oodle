<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event\PollRepository;

use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Domain\Repository\PollRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

final class FindPollsEvent
{
    /**
     * @param ConstraintInterface[] $constraints
     * @param QueryInterface<BasePoll> $query
     * @param PollRepository $caller
     */
    public function __construct(
        private array $constraints,
        private readonly QueryInterface $query,
        private readonly PollRepository $caller,
    ) {}

    /**
     * @return ConstraintInterface[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * @return QueryInterface<BasePoll>
     */
    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    /**
     * @param ConstraintInterface[] $constraints
     */
    public function setConstraints(array $constraints): void
    {
        $this->constraints = $constraints;
    }

    public function getCaller(): PollRepository
    {
        return $this->caller;
    }
}
