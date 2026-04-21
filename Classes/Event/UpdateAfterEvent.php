<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use Psr\Http\Message\ResponseInterface;

final class UpdateAfterEvent
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private readonly Poll $poll,
        private readonly int $voteCount,
        private readonly bool $areOptionsModified,
        private bool $continue,
        private readonly array $settings,
        private readonly PollController $caller,
        private ResponseInterface $response,
    ) {}

    public function getPoll(): Poll
    {
        return $this->poll;
    }
    public function getVoteCount(): int
    {
        return $this->voteCount;
    }
    public function getAreOptionsModified(): bool
    {
        return $this->areOptionsModified;
    }

    public function getContinue(): bool
    {
        return $this->continue;
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

    public function setContinue(bool $continue): void
    {
        $this->continue = $continue;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
