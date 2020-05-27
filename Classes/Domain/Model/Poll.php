<?php
namespace T3\T3oodle\Domain\Model;

use T3\T3oodle\Utility\DateTimeUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***
 *
 * This file is part of the "t3oodle" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 
 *
 ***/

/**
 * Poll
 */
class Poll extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $link = '';

    /**
     * @var string
     */
    protected $slug = '';

    /**
     * @var string
     */
    protected $type = \T3\T3oodle\Domain\Enumeration\PollType::SIMPLE;

    /**
     * @var string
     */
    protected $visibility = \T3\T3oodle\Domain\Enumeration\Visbility::LISTED;

    /**
     * author
     * 
     * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    protected $author;

    /**
     * @var string
     */
    protected $authorName = '';

    /**
     * @var string
     */
    protected $authorMail = '';

    /**
     * @var string
     */
    protected $authorIdent = '';

    /**
     * options
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\T3oodle\Domain\Model\Option>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $options;

    /**
     * @var bool
     */
    protected $settingTristateCheckbox = false;

    /**
     * @var int
     */
    protected $settingMaxVotesPerOption = 0;

    /**
     * @var bool
     */
    protected $settingOneOptionOnly = false;

    /**
     * @var bool
     */
    protected $settingAnonymousVoting = false;

    /**
     * @var \DateTime|null
     */
    protected $settingVotingExpiresDate;
    /**
     * @var \DateTime|null
     */
    protected $settingVotingExpiresTime;

    /**
     * @var bool
     */
    protected $isPublished = false;

    /**
     * @var \DateTime|null
     */
    protected $publishDate;

    /**
     * @var bool
     */
    protected $isFinished = false;

    /**
     * @var \DateTime|null
     */
    protected $finishDate;

    /**
     * @var \T3\T3oodle\Domain\Model\Option
     */
    protected $finalOption;

    /**
     * votes
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\T3oodle\Domain\Model\Vote>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $votes;


    public function __construct()
    {
        $this->options = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->votes = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): void
    {
        $this->visibility = $visibility;
    }

    public function getAuthor(): ?\TYPO3\CMS\Extbase\Domain\Model\FrontendUser
    {
        return $this->author;
    }

    public function setAuthor(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $author)
    {
        $this->author = $author;
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): void
    {
        $this->authorName = $authorName;
    }

    public function getAuthorMail(): string
    {
        return $this->authorMail;
    }

    public function setAuthorMail(string $authorMail): void
    {
        $this->authorMail = $authorMail;
    }

    public function getAuthorIdent(): string
    {
        return $this->authorIdent;
    }

    public function setAuthorIdent(string $authorIdent): void
    {
        $this->authorIdent = $authorIdent;
    }

    public function addOption(\T3\T3oodle\Domain\Model\Option $option): void
    {
        $option->setParent($this);
        $this->options->attach($option);
    }

    public function removeOption(\T3\T3oodle\Domain\Model\Option $optionToRemove): void
    {
        $this->options->detach($optionToRemove);
    }

    /**
     * @param bool $skipMarkedToDeleted When true, only options are returned, which are not marked to get deleted
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\T3oodle\Domain\Model\Option> $options
     */
    public function getOptions($skipMarkedToDeleted = false): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        if ($skipMarkedToDeleted) {
            $options = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class);
            foreach ($this->options as $option) {
                if (!$option->isMarkToDelete()) {
                    $options->attach($option);
                }
            }
            return $options;
        }
        return $this->options;
    }

    public function setOptions(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $options): void
    {
        $this->options = $options;
    }

    public function isSettingTristateCheckbox(): bool
    {
        return $this->settingTristateCheckbox;
    }

    public function setSettingTristateCheckbox(bool $settingTristateCheckbox): void
    {
        $this->settingTristateCheckbox = $settingTristateCheckbox;
    }

    public function getSettingMaxVotesPerOption(): int
    {
        return $this->settingMaxVotesPerOption;
    }

    public function setSettingMaxVotesPerOption(int $settingMaxVotesPerOption): void
    {
        $this->settingMaxVotesPerOption = $settingMaxVotesPerOption;
    }

    public function isSettingOneOptionOnly(): bool
    {
        return $this->settingOneOptionOnly;
    }

    public function setSettingOneOptionOnly(bool $settingOneOptionOnly): void
    {
        $this->settingOneOptionOnly = $settingOneOptionOnly;
    }

    public function isSettingAnonymousVoting(): bool
    {
        return $this->settingAnonymousVoting;
    }

    public function setSettingAnonymousVoting(bool $settingAnonymousVoting): void
    {
        $this->settingAnonymousVoting = $settingAnonymousVoting;
    }

    public function getSettingVotingExpiresDate(): ?\DateTime
    {
        return $this->settingVotingExpiresDate;
    }

    public function setSettingVotingExpiresDate(?\DateTime $settingVotingExpiresDate): void
    {
        $this->settingVotingExpiresDate = $settingVotingExpiresDate;
    }

    public function getSettingVotingExpiresTime(): ?\DateTime
    {
        return $this->settingVotingExpiresTime;
    }




    public function setSettingVotingExpiresTime(?\DateTime $settingVotingExpiresTime): void
    {
        $this->settingVotingExpiresTime = $settingVotingExpiresTime;
    }

    /**
     * Combines date and time field
     *
     * @return \DateTime|null
     */
    public function getSettingVotingExpiresAt(): ?\DateTime
    {
        if ($this->getSettingVotingExpiresDate() && $this->getSettingVotingExpiresTime()) {
            return $this->getSettingVotingExpiresDate()->modify($this->getSettingVotingExpiresTime()->format('H:i:s'));
        }
        return null;
    }

    public function isVotingExpired(): bool
    {
        if (!$this->getSettingVotingExpiresAt()) {
            return false;
        }
        return DateTimeUtility::now()->getTimestamp() > $this->getSettingVotingExpiresAt()->getTimestamp();
    }

    public function addVote(\T3\T3oodle\Domain\Model\Vote $vote): void
    {
        $vote->setParent($this);
        $this->votes->attach($vote);
    }

    public function removeVote(\T3\T3oodle\Domain\Model\Vote $voteToRemove): void
    {
        $this->votes->detach($voteToRemove);
    }

    public function getVotes(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->votes;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\T3oodle\Domain\Model\Vote> $votes
     * @return void
     */
    public function setVotes(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $votes): void
    {
        $this->votes = $votes;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): void
    {
        $this->isPublished = $isPublished;
    }

    public function getPublishDate(): ?\DateTime
    {
        return $this->publishDate;
    }

    public function setPublishDate(?\DateTime $publishDate): void
    {
        $this->publishDate = $publishDate;
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(bool $isFinished): void
    {
        $this->isFinished = $isFinished;
    }

    public function getFinishDate(): ?\DateTime
    {
        return $this->finishDate;
    }

    public function setFinishDate(?\DateTime $finishDate): void
    {
        $this->finishDate = $finishDate;
    }

    public function getFinalOption(): ?Option
    {
        return $this->finalOption;
    }

    public function setFinalOption(Option $finalOption): void
    {
        $this->finalOption = $finalOption;
    }

    /**
     * @return array key is uid of option, value the amount of votes
     */
    public function getOptionTotals(): array
    {
        $optionTotals = [];
        foreach ($this->getVotes() as $vote) {
            foreach ($vote->getOptionValues() as $optionValue) {
                if ($optionValue->getOption()) {
                    if (!array_key_exists($optionValue->getOption()->getUid(), $optionTotals)) {
                        $optionTotals[$optionValue->getOption()->getUid()] = 0;
                    }
                    if ($optionValue->getValue() === '1') {
                        $optionTotals[$optionValue->getOption()->getUid()]++;
                    }
                }
            }
        }
        return $optionTotals;
    }

    public function areOptionsModified(): bool
    {
        $attributes = ['name', 'markToDelete', 'uid'];
        foreach ($this->getOptions() as $option) {
            $cleanProps = $option->_getCleanProperties();
            $props = $option->_getProperties();
            foreach ($attributes as $attribute) {
                if ($cleanProps[$attribute] !== $props[$attribute]) {
                    return true;
                }
            }
        }
        return false;
    }
}
