<?php declare(strict_types=1);
namespace T3\T3oodle\Slots;

use T3\T3oodle\Controller\PollController;
use T3\T3oodle\Domain\Model\Poll;
use T3\T3oodle\Domain\Repository\PollRepository;
use T3\T3oodle\Utility\SlugUtility;
use T3\T3oodle\Utility\TranslateUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class UpdatePollSlug
{
    public function afterCreate(
        Poll $poll,
        bool $publishDirectly,
        bool $continue,
        array $settings,
        PollController $caller
    )
    {
        $this->updatePollSlug($poll);
    }

    public function beforeUpdate(
        Poll $poll,
        int $voteCount,
        bool $areOptionsModified,
        bool $continue,
        array $settings,
        PollController $caller
    ) {
        $visibilityBefore = $poll->_getCleanProperty('visibility');
        $visibilityAfter = $poll->getVisibility();
        $titleBefore = $poll->_getCleanProperty('title');
        $titleAfter = $poll->getTitle();

        if ($visibilityBefore !== $visibilityAfter || $titleBefore !== $titleAfter) {
            $slugBefore = $poll->getSlug();
            $this->updatePollSlug($poll);
            $caller->addFlashMessage(
                TranslateUtility::translate('flash.slugChanged', [$slugBefore, $poll->getSlug()]),
                '',
                AbstractMessage::WARNING
            );
        }
    }

    /**
     * When visibility is not listed a random slug is generated.
     * Otherwise the slug utility takes the poll title to generate the slug.
     * If the generated slug is not unique, the uid of the poll is appended.
     *
     * @param Poll $poll
     */
    protected function updatePollSlug(Poll $poll): void
    {
        $slugUtility = GeneralUtility::makeInstance(SlugUtility::class, 'tx_t3oodle_domain_model_poll', 'slug');
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $pollRepo = $objectManager->get(PollRepository::class);

        // Create slug and update created entity
        if ($poll->getVisibility() === \T3\T3oodle\Domain\Enumeration\Visibility::NOT_LISTED) {
            $poll->setSlug($slugUtility->sanitize(uniqid('', true) . $poll->getUid()));
        } else {
            $newSlug = $slugUtility->sanitize($poll->getTitle());
            if ($pollRepo->countBySlug($newSlug) > 0) {
                $newSlug .= '-' . $poll->getUid();
            }
            $poll->setSlug($newSlug);
        }

        $pollRepo->update($poll);
        $persistenceManager = $objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
    }
}
