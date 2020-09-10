<?php
namespace FGTCLB\T3oodle\Domain\Model;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Enumeration\PollStatus;
use FGTCLB\T3oodle\Domain\Permission\PollPermission;
use FGTCLB\T3oodle\Traits\Model\DynamicUserProperties;
use FGTCLB\T3oodle\Utility\DateTimeUtility;
use FGTCLB\T3oodle\Utility\SettingsUtility;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Poll extends AbstractEntity
{
    use DynamicUserProperties;

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
    protected $type = \FGTCLB\T3oodle\Domain\Enumeration\PollType::SIMPLE;

    /**
     * @var string
     */
    protected $visibility = \FGTCLB\T3oodle\Domain\Enumeration\Visibility::LISTED;

    /**
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
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FGTCLB\T3oodle\Domain\Model\Option>
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
    protected $settingSecretParticipants = false;

    /**
     * @var bool
     */
    protected $settingSecretVotings = false;

    /**
     * @var bool Note: When Super Secret Mode is true, both previous secret settings are true as well
     */
    protected $settingSuperSecretMode = false;

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
     * @var \FGTCLB\T3oodle\Domain\Model\Option
     */
    protected $finalOption;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FGTCLB\T3oodle\Domain\Model\Vote>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $votes;

    /**
     * @var array|null
     */
    protected static $availableOptionsCache;


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
        if ($this->getAuthor()) {
            return $this->getPropertyDynamically($this->getAuthor(), 'name');
        }
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): void
    {
        $this->authorName = $authorName;
    }

    public function getAuthorMail(): string
    {
        if ($this->getAuthor()) {
            return $this->getPropertyDynamically($this->getAuthor(), 'mail', false);
        }
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

    public function addOption(\FGTCLB\T3oodle\Domain\Model\Option $option): void
    {
        $option->setPoll($this);
        $this->options->attach($option);
    }

    public function removeOption(\FGTCLB\T3oodle\Domain\Model\Option $optionToRemove): void
    {
        $this->options->detach($optionToRemove);
    }

    /**
     * @param bool $skipMarkedToDeleted When true, only options are returned, which are not marked to get deleted
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FGTCLB\T3oodle\Domain\Model\Option> $options
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

    public function isSettingSecretParticipants(): bool
    {
        return $this->settingSecretParticipants;
    }

    public function setSettingSecretParticipants(bool $settingSecretParticipants): void
    {
        $this->settingSecretParticipants = $settingSecretParticipants;
    }

    public function isSettingSecretVotings(): bool
    {
        return $this->settingSecretVotings;
    }

    public function setSettingSecretVotings(bool $settingSecretVotings): void
    {
        $this->settingSecretVotings = $settingSecretVotings;
    }

    public function isSettingSuperSecretMode(): bool
    {
        return $this->settingSuperSecretMode;
    }

    public function setSettingSuperSecretMode(bool $settingSuperSecretMode): void
    {
        $this->settingSuperSecretMode = $settingSuperSecretMode;
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
        if ($settingVotingExpiresTime) {
            $settingVotingExpiresTime->modify('1970-01-01');
        }
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

    public function addVote(\FGTCLB\T3oodle\Domain\Model\Vote $vote): void
    {
        $vote->setPoll($this);
        $this->votes->attach($vote);
    }

    public function removeVote(\FGTCLB\T3oodle\Domain\Model\Vote $voteToRemove): void
    {
        $this->votes->detach($voteToRemove);
    }

    public function getVotes(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->votes;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FGTCLB\T3oodle\Domain\Model\Vote> $votes
     * @return void
     */
    public function setVotes(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $votes): void
    {
        $this->votes = $votes;
    }

    public function getIsCurrentUserAuthor(): bool
    {
        return $this->getAuthorIdent() === UserIdentUtility::getCurrentUserIdent();
    }

    public function getHasCurrentUserVoted(): bool
    {
        foreach ($this->getVotes() as $vote) {
            if ($vote->getParticipantIdent() === UserIdentUtility::getCurrentUserIdent()) {
                return true;
            }
        }
        return false;
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
        $settings = SettingsUtility::getTypoScriptSettings();
        $optionTotals = [];
        foreach ($this->getVotes() as $vote) {
            foreach ($vote->getOptionValues() as $optionValue) {
                if ($optionValue->getOption()) {
                    if (!array_key_exists($optionValue->getOption()->getUid(), $optionTotals)) {
                        $optionTotals[$optionValue->getOption()->getUid()] = 0;
                    }
                    if ($optionValue->getValue() === '1' ||
                        ($settings['countMaybeVotes'] && $optionValue->getValue() === '2')
                    ) {
                        $optionTotals[$optionValue->getOption()->getUid()]++;
                    }
                }
            }
        }
        return $optionTotals;
    }

    public function areOptionsModified(): bool
    {
        // Check options for changes
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

        // Check related poll settings (all but expiration date)
        $attributes = [
            'settingOneOptionOnly',
            'settingMaxVotesPerOption',
            'settingTristateCheckbox',
            'settingSecretParticipants',
            'settingSecretVotings',
            'settingSuperSecretMode'
        ];
        $cleanProps = $this->_getCleanProperties();
        $props = $this->_getProperties();
        foreach ($attributes as $attribute) {
            if ($cleanProps[$attribute] !== $props[$attribute]) {
                return true;
            }
        }
        return false;
    }

    public function getAvailableOptions(): array
    {
        if (!$this->getSettingMaxVotesPerOption()) {
            return $this->getOptions()->toArray();
        }
        if (self::$availableOptionsCache === null) {
            $settings = SettingsUtility::getTypoScriptSettings();
            $countMaybeVotes = (bool) $settings['countMaybeVotes'];

            $usedOptions = [];
            $usedOptionCounts = [];
            foreach ($this->getOptions() as $option) {
                $usedOptions[$option->getUid()] = $option;
                $usedOptionCounts[$option->getUid()] = 0;
            }

            foreach ($this->getVotes() as $vote) {
                foreach ($vote->getOptionValues() as $optionValue) {
                    if ($optionValue->getValue() === '1' || ($countMaybeVotes && $optionValue->getValue() === '2')) {
                        $usedOptionCounts[$optionValue->getOption()->getUid()]++;
                    }
                }
            }

            $availableOptions = [];
            foreach ($usedOptionCounts as $usedOptionUid => $usagesAmount) {
                if ($usagesAmount < $this->getSettingMaxVotesPerOption()) {
                    $availableOptions[] = $usedOptions[$usedOptionUid];
                }
            }
            self::$availableOptionsCache = $availableOptions;
        }
        return self::$availableOptionsCache;
    }

    public function getStatus(): PollStatus
    {
        if (!$this->isPublished()) {
            return new PollStatus(PollStatus::DRAFT);
        }
        if ($this->isFinished()) {
            return new PollStatus(PollStatus::FINISHED);
        }

        $pollPermission = GeneralUtility::makeInstance(PollPermission::class);
        if ($pollPermission->isVotingAllowed($this) && count($this->getAvailableOptions()) > 0) {
            return new PollStatus(PollStatus::OPENED);
        }
        return new PollStatus(PollStatus::CLOSED);
    }

    public function getPartialName(): string
    {
        return ucfirst($this->getType());
    }
}
