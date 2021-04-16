<?php

declare(strict_types = 1);

namespace FGTCLB\T3oodle\Domain\Model\Dto;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Model\Option;
use FGTCLB\T3oodle\Traits\Model\CreatorTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SuggestionDto
{
    use CreatorTrait;

    /**
     * @var \FGTCLB\T3oodle\Domain\Model\BasePoll|null
     */
    private $poll;

    /**
     * @var string
     */
    private $suggestion;

    public function __construct(
        \FGTCLB\T3oodle\Domain\Model\BasePoll $poll = null,
        string $suggestion = '',
        \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $creator = null,
        string $creatorName = '',
        string $creatorMail = '',
        string $creatorIdent = ''
    ) {
        $this->poll = $poll;
        $this->suggestion = $suggestion;
        $this->creator = $creator;
        $this->creatorName = $creatorName;
        $this->creatorMail = $creatorMail;
        $this->creatorIdent = $creatorIdent;
    }

    public function getPoll(): \FGTCLB\T3oodle\Domain\Model\BasePoll
    {
        return $this->poll;
    }

    public function setPoll(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): void
    {
        $this->poll = $poll;
    }

    public function getSuggestion(): string
    {
        return $this->suggestion;
    }

    public function setSuggestion(string $suggestion): void
    {
        $this->suggestion = $suggestion;
    }

    public function makeOption(): Option
    {
        /** @var Option $newOption */
        $newOption = GeneralUtility::makeInstance(Option::class);
        $newOption->setPid($this->getPoll()->getPid());
        $newOption->setName(trim($this->getSuggestion()));

        if ($this->getCreator()) {
            $newOption->setCreator($this->getCreator());
        }
        if ($this->getCreatorName()) {
            $newOption->setCreatorName($this->getCreatorName());
        }
        if ($this->getCreatorMail()) {
            $newOption->setCreatorMail($this->getCreatorMail());
        }
        if ($this->getCreatorIdent()) {
            $newOption->setCreatorIdent($this->getCreatorIdent());
        }

        $this->getPoll()->addOption($newOption);

        return $newOption;
    }
}
