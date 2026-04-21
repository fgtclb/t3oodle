<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Domain\Model;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Traits\Model\CreatorTrait;
use FGTCLB\T3oodle\Traits\Model\MarkToDeleteTrait;
use FGTCLB\T3oodle\Traits\Model\RecordDatePropertiesTrait;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Option extends AbstractEntity
{
    use MarkToDeleteTrait;
    use RecordDatePropertiesTrait;
    use CreatorTrait;

    /**
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $name = '';

    /**
     * @var int
     */
    protected $sorting = 0;

    /**
     * @var BasePoll
     */
    protected $poll;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(?int $sorting): void
    {
        $this->sorting = (int)$sorting;
    }

    public function getPoll(): BasePoll
    {
        return $this->poll;
    }

    public function setPoll(BasePoll $poll): void
    {
        $this->poll = $poll;
    }

    /**
     * @return array{
     *     -1: string,
     *     0: string,
     *     1: string,
     *     2?: string
     * }
     */
    public function getCheckboxStates(): array
    {
        $states = [
            -1 => '',
            0 => 'no',
            1 => 'yes',
        ];
        if ($this->poll->isSettingTristateCheckbox()) {
            $states[2] = 'maybe';
        }

        return $states;
    }

    public function isFull(): bool
    {
        $votesLeft = $this->getAmountOfLeftVotes() ?? 1;

        return $votesLeft === 0;
    }

    public function getAmountOfLeftVotes(bool $respectCurrentUser = false): ?int
    {
        if ($this->poll->getSettingMaxVotesPerOption() === 0) {
            return null;
        }

        $total = 0;
        $currentVotes = $this->poll->getVotes();
        foreach ($currentVotes as $vote) {
            if ($respectCurrentUser || $vote->getParticipantIdent() !== UserIdentUtility::getCurrentUserIdent()) {
                foreach ($vote->getOptionValues() as $optionValue) {
                    if ($optionValue->getOption() === $this && $optionValue->getValue() === '1') {
                        ++$total;
                    }
                }
            }
        }

        return $this->poll->getSettingMaxVotesPerOption() - $total;
    }

    public function getAmountOfLeftVotesRespectingCurrentUser(): ?int
    {
        return $this->getAmountOfLeftVotes(true);
    }
}
