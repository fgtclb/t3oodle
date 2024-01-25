<?php
declare(strict_types=1);

namespace FGTCLB\T3oodle\Event;

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto;

class UpdateSuggestionBeforeEvent {
    private SuggestionDto $suggestionDto;
    private bool $continue;
    private array $settings;
    private PollController $caller;

    public function __construct(SuggestionDto $suggestionDto, bool $continue, array $settings, PollController $caller) {
        $this->suggestionDto = $suggestionDto;
        $this->continue = $continue;
        $this->settings = $settings;
        $this->caller = $caller;
    }

    public function getSuggestionDto(): SuggestionDto {
        return $this->suggestionDto;
    }

    public function getContinue(): bool {
        return $this->continue;
    }

    public function getSettings(): array {
        return $this->settings;
    }

    public function getCaller(): PollController {
        return $this->caller;
    }

    public function setContinue(bool $continue): void {
        $this->continue = $continue;
    }
}
