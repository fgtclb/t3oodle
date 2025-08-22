<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\BasePoll as Poll;
use Psr\Http\Message\ResponseInterface;

final class CreateAfterEvent
{
    private Poll $poll;
    private bool $publishDirectly;
    private bool $continue;
    private array $settings;
    private PollController $caller;
    private ResponseInterface $response;

    public function __construct(Poll $poll, bool $publishDirectly, bool $continue, array $settings, PollController $caller, ResponseInterface $response)
    {
        $this->poll = $poll;
        $this->publishDirectly = $publishDirectly;
        $this->continue = $continue;
        $this->settings = $settings;
        $this->caller = $caller;
        $this->response = $response;
    }

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
