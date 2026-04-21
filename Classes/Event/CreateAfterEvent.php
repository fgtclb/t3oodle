<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use Psr\Http\Message\ResponseInterface;

final class CreateAfterEvent
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        private readonly Poll $poll,
        private readonly bool $publishDirectly,
        private bool $continue,
        private readonly array $settings,
        private readonly PollController $caller,
        private ResponseInterface $response,
    ) {}

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function getPublishDirectly(): bool
    {
        return $this->publishDirectly;
    }

    public function getContinue(): bool
    {
        return $this->continue;
    }

    public function setContinue(bool $continue): void
    {
        $this->continue = $continue;
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

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
