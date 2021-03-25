<?php

declare(strict_types = 1);

namespace FGTCLB\T3oodle\Slots;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Domain\Model\Poll;
use FGTCLB\T3oodle\Domain\Repository\PollRepository;
use FGTCLB\T3oodle\Utility\SlugUtility;
use FGTCLB\T3oodle\Utility\TranslateUtility;
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
    ): void {
        $this->updatePollSlug($poll);
    }

    public function beforeUpdate(
        Poll $poll,
        int $voteCount,
        bool $areOptionsModified,
        bool $continue,
        array $settings,
        PollController $caller
    ): void {
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
     */
    protected function updatePollSlug(Poll $poll): void
    {
        $slugUtility = GeneralUtility::makeInstance(SlugUtility::class, 'tx_t3oodle_domain_model_poll', 'slug');
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $pollRepo = $objectManager->get(PollRepository::class);

        // Create slug and update created entity
        if (\FGTCLB\T3oodle\Domain\Enumeration\Visibility::NOT_LISTED === $poll->getVisibility()) {
            $poll->setSlug($slugUtility->sanitize(uniqid('', true) . $poll->getUid()));
        } else {
            $newSlug = $slugUtility->sanitize($poll->getTitle());
            if (empty($newSlug)) {
                $newSlug = 'poll-' . $poll->getUid();
            } else {
                if ($pollRepo->countBySlug($newSlug) > 0) {
                    $newSlug .= '-' . $poll->getUid();
                }
            }
            $poll->setSlug($newSlug);
        }

        $pollRepo->update($poll);
        $persistenceManager = $objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
    }
}
