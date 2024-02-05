<?php
declare(strict_types=1);

namespace FGTCLB\T3oodle\Event\PollRepository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use FGTCLB\T3oodle\Domain\Repository\PollRepository;

class FindPollsEvent
{
    private $constraints;
    private $query;
    private $caller;

    public function __construct(
        array $constraints,
        QueryInterface $query,
        PollRepository $caller
    ) {
        $this->constraints = $constraints;
        $this->query = $query;
        $this->caller = $caller;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    public function getCaller(): PollRepository
    {
        return $this->caller;
    }
}
