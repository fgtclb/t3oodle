<?php

namespace FGTCLB\T3oodle\Controller;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Validator\CustomPollValidator;
use FGTCLB\T3oodle\Exception\AccessDeniedException;
use FGTCLB\T3oodle\Traits\ControllerValidatorManipulatorTrait;
use FGTCLB\T3oodle\Utility\CookieUtility;
use FGTCLB\T3oodle\Utility\DateTimeUtility;
use FGTCLB\T3oodle\Utility\TranslateUtility;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class PollController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    use ControllerValidatorManipulatorTrait;

    /**
     * @var mixed
     */
    protected $currentUser;

    /**
     * @var string UID of current frontend user or random string used to identify user by cookie
     */
    protected $currentUserIdent = '';

    /**
     * @var \FGTCLB\T3oodle\Domain\Permission\PollPermission
     */
    protected $pollPermission;

    /**
     * @var \FGTCLB\T3oodle\Domain\Repository\PollRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $pollRepository;

    /**
     * @var \FGTCLB\T3oodle\Domain\Repository\OptionRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $optionRepository;

    /**
     * @var \FGTCLB\T3oodle\Domain\Repository\VoteRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $voteRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $userRepository;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $signalSlotDispatcher;

    public function initializeAction(): void
    {
        $this->initializeCurrentUserOrUserIdent();

        $this->pollPermission = GeneralUtility::makeInstance(
            \FGTCLB\T3oodle\Domain\Permission\PollPermission::class,
            $this->currentUserIdent,
            $this->settings
        );

        $this->processPollAndVoteArgumentFromRequest();
        $this->addCalendarLabelsToSettings();

        $this->settings['_dynamic'] = new \stdClass();
    }

    public function initializeView(ViewInterface $view): void
    {
        $view->assign('contentObject', $this->configurationManager->getContentObject()->data);
    }

    /**
     * @return string|bool
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }

    public function listAction(): void
    {
        $this->pollRepository->setControllerSettings($this->settings);
        $polls = $this->pollRepository->findPolls(
            (bool)$this->settings['list']['draft'],
            (bool)$this->settings['list']['finished'],
            (bool)$this->settings['list']['personal']
        );
        $this->signalSlotDispatcher->dispatch(__CLASS__, 'list', [
            'polls' => $polls,
            'settings' => $this->settings,
            'view' => $this->view,
            'caller' => $this,
        ]);
        $this->view->assign('polls', $polls);
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function showAction(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): void
    {
        $this->pollPermission->isAllowed($poll, 'show', true);

        if ($this->getControllerContext()->getRequest()->getOriginalRequestMappingResults()->hasErrors()) {
            $this->addFlashMessage(
                TranslateUtility::translate('flash.votingErrorOccurred'),
                '',
                AbstractMessage::ERROR,
                false
            );
            $this->view->assign('validationErrorsExisting', true);
        }

        $vote = $this->voteRepository->findOneByPollAndParticipantIdent($poll, $this->currentUserIdent);
        if (!$vote) {
            $vote = GeneralUtility::makeInstance(\FGTCLB\T3oodle\Domain\Model\Vote::class);
            $vote->setPoll($poll);
            if ($this->currentUser) {
                $vote->setParticipant($this->currentUser);
            }
        }

        $newOptionValues = [];
        /** @var Request|null $originalRequest */
        $originalRequest = $this->request->getOriginalRequest();
        if ($originalRequest && $originalRequest->hasArgument('vote')) {
            foreach ($originalRequest->getArgument('vote')['optionValues'] as $optionValue) {
                $newOptionValues[$optionValue['option']['__identity']] = $optionValue['value'];
            }
        }

        $signal = $this->signalSlotDispatcher->dispatch(__CLASS__, 'show', [
            'poll' => $poll,
            'vote' => $vote,
            'newOptionValues' => $newOptionValues,
            'settings' => $this->settings,
            'view' => $this->view,
            'caller' => $this,
        ]);

        $this->view->assign('poll', $poll);
        $this->view->assign('vote', $vote);
        if (!empty($newOptionValues)) {
            $this->view->assign('newOptionValues', $signal['newOptionValues']);
        }

        if ($this->pollPermission->isAllowed($poll, 'suggestNewOptions')) {
            $this->view->assign(
                'mySuggestions',
                $this->optionRepository->findByPollAndCreatorIdent($poll, $this->currentUserIdent) ?? []
            );
        }
    }

    /**
     * @TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\CustomVoteValidator", param="vote")
     */
    public function voteAction(\FGTCLB\T3oodle\Domain\Model\Vote $vote): void
    {
        $this->pollPermission->isAllowed($vote->getPoll(), 'voting', true);

        if (!$this->currentUser) {
            if (!$this->currentUserIdent) {
                $this->currentUserIdent = UserIdentUtility::generateNewUserIdent();
            }
            $vote->setParticipantIdent($this->currentUserIdent);
            CookieUtility::set('userIdent', $this->currentUserIdent);
        } else {
            $vote->setParticipant($this->currentUser);
            $vote->setParticipantIdent($this->currentUserIdent);
        }

        if (!$vote->getUid() &&
            $this->voteRepository->findOneByPollAndParticipantIdent($vote->getPoll(), $this->currentUserIdent)
        ) {
            $this->redirect('show', null, null, ['poll' => $vote->getPoll()]);
        }

        $signal = $this->signalSlotDispatcher->dispatch(__CLASS__, 'vote', [
            'vote' => $vote,
            'isNew' => !$vote->getUid(),
            'settings' => $this->settings,
            'continue' => true,
            'caller' => $this,
        ]);

        if ($signal['continue']) {
            $this->voteRepository->add($vote);
            $this->addFlashMessage(
                TranslateUtility::translate($signal['isNew'] ? 'flash.votingSaved' : 'flash.votingUpdated'),
                '',
                AbstractMessage::OK
            );
            $this->redirect('show', null, null, ['poll' => $vote->getPoll()]);
        }
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function resetVotesAction(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): void
    {
        $this->pollPermission->isAllowed($poll, 'resetVotes', true);

        $signal = $this->signalSlotDispatcher->dispatch(__CLASS__, 'resetVotes', [
            'poll' => $poll,
            'continue' => true,
            'settings' => $this->settings,
            'caller' => $this,
        ]);

        if ($signal['continue']) {
            $count = count($poll->getVotes());
            foreach ($poll->getVotes() as $vote) {
                $this->voteRepository->remove($vote);
            }
            $this->addFlashMessage(
                TranslateUtility::translate('flash.votesSuccessfullyDeleted', [$count])
            );
            $this->redirect('show', null, null, ['poll' => $poll]);
        }
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("vote")
     */
    public function deleteOwnVoteAction(\FGTCLB\T3oodle\Domain\Model\Vote $vote): void
    {
        $this->pollPermission->isAllowed($vote, 'deleteOwnVote', true);

        $signal = $this->signalSlotDispatcher->dispatch(__CLASS__, 'deleteOwnVote', [
            'vote' => $vote,
            'participantName' => $vote->getParticipantName(),
            'continue' => true,
            'settings' => $this->settings,
            'caller' => $this,
        ]);

        if ($signal['continue']) {
            $this->voteRepository->remove($vote);
            $this->addFlashMessage(
                TranslateUtility::translate('flash.voteSuccessfullyDeleted')
            );
            $this->redirect('show', null, null, ['poll' => $vote->getPoll()]);
        }
    }

    /**
     * @param int $option uid to finish
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function finishAction(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll, int $option = 0): void
    {
        $this->pollPermission->isAllowed($poll, 'finish', true);
        if ($option > 0) {
            // Persist final option
            /** @var \FGTCLB\T3oodle\Domain\Model\Option $option */
            $option = $this->optionRepository->findByUid($option);
            $poll->setFinalOption($option);
            $poll->setFinishDate(DateTimeUtility::now());
            $poll->setIsFinished(true);

            $signal = $this->signalSlotDispatcher->dispatch(__CLASS__, 'finish', [
                'poll' => $poll,
                'finalOption' => $option,
                'continue' => true,
                'settings' => $this->settings,
                'view' => $this->view,
                'caller' => $this,
            ]);

            if ($signal['continue']) {
                $this->pollRepository->update($poll);
                $this->addFlashMessage(
                    TranslateUtility::translate('flash.successfullyFinished', [$poll->getTitle(), $option->getName()])
                );
                $this->redirect('show', null, null, ['poll' => $poll]);
            }
        } else {
            // Display options to choose final one
            $this->signalSlotDispatcher->dispatch(__CLASS__, 'showFinish', [
                'poll' => $poll,
                'settings' => $this->settings,
                'view' => $this->view,
                'caller' => $this,
            ]);
        }
        $this->view->assign('poll', $poll);
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function finishSuggestionModeAction(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): void
    {
        $this->pollPermission->isAllowed($poll, 'finishSuggestionMode', true);

        $poll->setIsSuggestModeFinished(true);

        $signal = $this->signalSlotDispatcher->dispatch(__CLASS__, 'finishSuggestionMode', [
            'poll' => $poll,
            'continue' => true,
            'settings' => $this->settings,
            'caller' => $this,
        ]);

        if ($signal['continue']) {
            $this->pollRepository->update($poll);
            $this->addFlashMessage(
                TranslateUtility::translate('flash.successfullyFinishedSuggestionMode', [$poll->getTitle()])
            );
            $this->redirect('show', null, null, ['poll' => $poll]);
        }
    }

    /**
     * @param \FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto $suggestionDto
     */
    public function newSuggestionAction(
        \FGTCLB\T3oodle\Domain\Model\BasePoll $poll,
        \FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto $suggestionDto = null
    ): void {
        $this->pollPermission->isAllowed($poll, 'suggestNewOptions', true);

        if (!$suggestionDto) {
            $suggestionDto = GeneralUtility::makeInstance(\FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto::class, $poll);
        }
        if ($this->currentUser) {
            $suggestionDto->setCreator($this->currentUser);
        }
        $this->signalSlotDispatcher->dispatch(__CLASS__, 'newSuggestion', [
            'poll' => $poll,
            'suggestionDto' => $suggestionDto,
            'settings' => $this->settings,
            'view' => $this->view,
            'caller' => $this,
        ]);
        $this->view->assign('suggestionDto', $suggestionDto);
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\SuggestionDtoValidator", param="suggestionDto")
     */
    public function createSuggestionAction(\FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto $suggestionDto): void
    {
        $this->pollPermission->isAllowed($suggestionDto->getPoll(), 'suggestNewOptions', true);

        if (!$this->currentUser) {
            if (!$this->currentUserIdent) {
                $this->currentUserIdent = base64_encode(uniqid('', true) . uniqid('', true));
            }
            $suggestionDto->setCreatorIdent($this->currentUserIdent);
            CookieUtility::set('userIdent', $this->currentUserIdent);
        } else {
            $suggestionDto->setCreator($this->currentUser);
            $suggestionDto->setCreatorIdent($this->currentUserIdent);
        }

        $newSuggestedOption = $suggestionDto->makeOption();

        $signalBefore = $this->signalSlotDispatcher->dispatch(__CLASS__, 'createSuggestionBefore', [
            'suggestionDto' => $suggestionDto,
            'continue' => true,
            'settings' => $this->settings,
            'caller' => $this,
        ]);

        if ($signalBefore['continue']) {
            $this->optionRepository->add($newSuggestedOption);

            if ($suggestionDto->getPoll()->isSchedulePoll()) {
                $this->optionRepository->updateSortingOfOptionsByDateTime($suggestionDto->getPoll());
            }

            $persistenceManager = $this->objectManager->get(PersistenceManager::class);
            $persistenceManager->persistAll();

            $signalAfter = $this->signalSlotDispatcher->dispatch(__CLASS__, 'createSuggestionAfter', [
                'suggestionDto' => $suggestionDto,
                'continue' => true,
                'settings' => $this->settings,
                'caller' => $this,
            ]);

            if ($signalAfter['continue']) {
                $this->addFlashMessage(
                    TranslateUtility::translate('flash.successfullyCreatedSuggestion', [$suggestionDto->getSuggestion(), $suggestionDto->getPoll()->getTitle()]),
                    '',
                    AbstractMessage::OK
                );
                $this->redirect('show', null, null, ['poll' => $suggestionDto->getPoll()->getUid()]);
            }
        }
    }

    public function editSuggestionAction(
        \FGTCLB\T3oodle\Domain\Model\Option $option,
        \FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto $suggestionDto = null
    ): void {
        $this->pollPermission->isAllowed($option->getPoll(), 'suggestNewOptions', true);
        if ($option->getCreatorIdent() !== $this->currentUserIdent) {
            throw new AccessDeniedException('You are trying to update a suggestion, which you did not create!');
        }
        if (!$suggestionDto) {
            /** @var \FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto $suggestionDto */
            $suggestionDto = GeneralUtility::makeInstance(
                \FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto::class,
                $option->getPoll(),
                $option->getName(),
                $option->getCreator(),
                $option->getCreatorName(),
                $option->getCreatorMail(),
                $option->getCreatorIdent()
            );
        }

        $this->signalSlotDispatcher->dispatch(__CLASS__, 'editSuggestion', [
            'option' => $option,
            'suggestionDto' => $suggestionDto,
            'settings' => $this->settings,
            'view' => $this->view,
            'caller' => $this,
        ]);

        $this->view->assign('suggestionDto', $suggestionDto);
        $this->view->assign('option', $option);
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\SuggestionDtoValidator", param="suggestionDto")
     */
    public function updateSuggestionAction(
        \FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto $suggestionDto,
        \FGTCLB\T3oodle\Domain\Model\Option $option
    ): void {
        $this->pollPermission->isAllowed($suggestionDto->getPoll(), 'suggestNewOptions', true);

        if (!$this->currentUser) {
            if (!$this->currentUserIdent) {
                $this->currentUserIdent = base64_encode(uniqid('', true) . uniqid('', true));
            }
            CookieUtility::set('userIdent', $this->currentUserIdent);
        }

        if ($option->getCreatorIdent() !== $this->currentUserIdent) {
            throw new AccessDeniedException('You are trying to update a suggestion, which you did not create!');
        }

        $option->setName(trim($suggestionDto->getSuggestion()));

        $signalBefore = $this->signalSlotDispatcher->dispatch(__CLASS__, 'updateSuggestionBefore', [
            'suggestionDto' => $suggestionDto,
            'continue' => true,
            'settings' => $this->settings,
            'caller' => $this,
        ]);

        if ($signalBefore['continue']) {
            $this->optionRepository->update($option);

            if ($suggestionDto->getPoll()->isSchedulePoll()) {
                $this->optionRepository->updateSortingOfOptionsByDateTime($suggestionDto->getPoll());
                $this->pollRepository->update($suggestionDto->getPoll());
            }

            $persistenceManager = $this->objectManager->get(PersistenceManager::class);
            $persistenceManager->persistAll();

            $signalAfter = $this->signalSlotDispatcher->dispatch(__CLASS__, 'updateSuggestionAfter', [
                'suggestionDto' => $suggestionDto,
                'continue' => true,
                'settings' => $this->settings,
                'caller' => $this,
            ]);

            if ($signalAfter['continue']) {
                $this->addFlashMessage(
                    TranslateUtility::translate('flash.successfullyUpdatedSuggestion', [$suggestionDto->getSuggestion(), $suggestionDto->getPoll()->getTitle()]),
                    '',
                    AbstractMessage::OK
                );
                $this->redirect('show', null, null, ['poll' => $suggestionDto->getPoll()->getUid()]);
            }
        }
    }

    public function deleteSuggestionAction(\FGTCLB\T3oodle\Domain\Model\Option $option): void
    {
        $poll = $option->getPoll();
        $this->pollPermission->isAllowed($poll, 'suggestNewOptions', true);

        if (!$this->currentUser) {
            if (!$this->currentUserIdent) {
                $this->currentUserIdent = base64_encode(uniqid('', true) . uniqid('', true));
            }
            CookieUtility::set('userIdent', $this->currentUserIdent);
        }

        if ($option->getCreatorIdent() !== $this->currentUserIdent) {
            throw new AccessDeniedException('You are trying to update a suggestion, which you did not create!');
        }

        $signal = $this->signalSlotDispatcher->dispatch(__CLASS__, 'deleteSuggestion', [
            'option' => $option,
            'continue' => true,
            'settings' => $this->settings,
            'caller' => $this,
        ]);

        if ($signal['continue']) {
            $this->optionRepository->remove($option);
            $this->addFlashMessage(TranslateUtility::translate('flash.successfullyDeletedSuggestion', [$option->getName()]));
            $this->redirect('show', null, null, ['poll' => $poll->getUid()]);
        }
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function newAction(
        \FGTCLB\T3oodle\Domain\Model\BasePoll $poll = null,
        bool $publishDirectly = true,
        string $pollType = \FGTCLB\T3oodle\Domain\Model\SimplePoll::class
    ): void {
        if (!$poll) {
            $poll = GeneralUtility::makeInstance($pollType);
            if ($this->currentUser) {
                $poll->setAuthor($this->currentUser);
            }
        }

        if ($poll instanceof \FGTCLB\T3oodle\Domain\Model\SimplePoll) {
            $this->pollPermission->isAllowed($poll, 'newSimplePoll', true);
        } else {
            $this->pollPermission->isAllowed($poll, 'newSchedulePoll', true);
        }

        $newOptions = [];
        /** @var Request|null $originalRequest */
        $originalRequest = $this->request->getOriginalRequest();
        if ($originalRequest) {
            $newOptions = $this->request->getOriginalRequest()->getArgument('poll')['options'];
        }

        $signal = $this->signalSlotDispatcher->dispatch(__CLASS__, 'new', [
            'poll' => $poll,
            'publishDirectly' => $publishDirectly,
            'newOptions' => $newOptions,
            'settings' => $this->settings,
            'view' => $this->view,
            'caller' => $this,
        ]);

        $this->view->assign('poll', $poll);
        $this->view->assign('publishDirectly', $signal['publishDirectly']);
        if (!empty($signal['newOptions'])) {
            $this->view->assign('newOptions', $signal['newOptions']);
        }
    }

    public function initializeCreateAction(): void
    {
        $this->disableValidator('poll', CustomPollValidator::class);
        /** @var \TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator $validator */
        $validator = $this->arguments->getArgument('poll')->getValidator();
        $validator->addValidator(new CustomPollValidator(['action' => 'create']));
    }

    /**
     * @TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\CustomPollValidator", param="poll")
     * @\TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\AcceptedTermsValidator", param="acceptTerms")
     */
    public function createAction(
        \FGTCLB\T3oodle\Domain\Model\BasePoll $poll,
        bool $publishDirectly,
        bool $acceptTerms = false
    ): void {
        if ($poll->isSimplePoll()) {
            $this->pollPermission->isAllowed($poll, 'newSimplePoll', true);
        } else {
            $this->pollPermission->isAllowed($poll, 'newSchedulePoll', true);
        }
        if (!$this->currentUser) {
            if (!$this->currentUserIdent) {
                $this->currentUserIdent = base64_encode(uniqid('', true) . uniqid('', true));
            }
            $poll->setAuthorIdent($this->currentUserIdent);
            CookieUtility::set('userIdent', $this->currentUserIdent);
        } else {
            $poll->setAuthor($this->currentUser);
            $poll->setAuthorIdent($this->currentUserIdent);
        }

        $signalBefore = $this->signalSlotDispatcher->dispatch(__CLASS__, 'createBefore', [
            'poll' => $poll,
            'publishDirectly' => $publishDirectly,
            'continue' => true,
            'settings' => $this->settings,
            'caller' => $this,
        ]);

        if ($signalBefore['continue']) {
            $this->pollRepository->add($poll);

            $persistenceManager = $this->objectManager->get(PersistenceManager::class);
            $persistenceManager->persistAll();

            $signalAfter = $this->signalSlotDispatcher->dispatch(__CLASS__, 'createAfter', [
                'poll' => $poll,
                'publishDirectly' => $publishDirectly,
                'continue' => true,
                'settings' => $this->settings,
                'caller' => $this,
            ]);

            if ($signalAfter['continue']) {
                $this->addFlashMessage(
                    TranslateUtility::translate('flash.successfullyCreated', [$poll->getTitle()]),
                    '',
                    AbstractMessage::OK
                );
                if ($publishDirectly) {
                    $this->forward('publish', null, null, ['poll' => $poll]);
                }
                $this->redirect('show', null, null, ['poll' => $poll->getUid()]);
            }
        }
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function publishAction(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): void
    {
        $this->pollPermission->isAllowed($poll, 'publish', true);
        $poll->setPublishDate(DateTimeUtility::now());
        $poll->setIsPublished(true);

        $signal = $this->signalSlotDispatcher->dispatch(__CLASS__, 'publish', [
            'poll' => $poll,
            'continue' => true,
            'settings' => $this->settings,
            'caller' => $this,
        ]);

        if ($signal['continue']) {
            $this->pollRepository->update($poll);
            $this->addFlashMessage(
                TranslateUtility::translate('flash.successfullyPublished', [$poll->getTitle()]),
                '',
                AbstractMessage::OK
            );
            $this->redirect('show', null, null, ['poll' => $poll]);
        }
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function editAction(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): void
    {
        $this->pollPermission->isAllowed($poll, 'edit', true);

        $this->signalSlotDispatcher->dispatch(__CLASS__, 'edit', [
            'poll' => $poll,
            'settings' => $this->settings,
            'view' => $this->view,
            'caller' => $this,
        ]);

        $this->view->assign('poll', $poll);
    }

    /**
     * @TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\CustomPollValidator", param="poll")
     */
    public function updateAction(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): void
    {
        $this->pollPermission->isAllowed($poll, 'edit', true);

        $voteCount = count($poll->getVotes());
        $optionsModified = $poll->areOptionsModified();

        $signalBefore = $this->signalSlotDispatcher->dispatch(__CLASS__, 'updateBefore', [
            'poll' => $poll,
            'voteCount' => $voteCount,
            'areOptionsModified' => $optionsModified,
            'continue' => true,
            'settings' => $this->settings,
            'caller' => $this,
        ]);

        if ($signalBefore['continue']) {
            if ($voteCount > 0 && $optionsModified) {
                foreach ($poll->getVotes() as $vote) {
                    $this->voteRepository->remove($vote);
                }
            }
            $this->removeMarkedPollOptions($poll);
            $this->pollRepository->update($poll);

            $persistenceManager = $this->objectManager->get(PersistenceManager::class);
            $persistenceManager->persistAll();

            $signalAfter = $this->signalSlotDispatcher->dispatch(__CLASS__, 'updateAfter', [
                'poll' => $poll,
                'voteCount' => $voteCount,
                'areOptionsModified' => $optionsModified,
                'continue' => true,
                'settings' => $this->settings,
                'caller' => $this,
            ]);

            if ($signalAfter['continue']) {
                $this->addFlashMessage(TranslateUtility::translate('flash.successfullyUpdated', [$poll->getTitle()]));
                if ($signalAfter['voteCount'] > 0 && $signalAfter['areOptionsModified']) {
                    $this->addFlashMessage(
                        TranslateUtility::translate('flash.noticeRemovedVotes', [$signalAfter['voteCount']]),
                        '',
                        AbstractMessage::WARNING
                    );
                }
                $this->redirect('show', null, null, ['poll' => $poll]);
            }
        }
    }

    /**
     * @TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\CustomPollValidator", param="poll")
     */
    public function deleteAction(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): void
    {
        $this->pollPermission->isAllowed($poll, 'delete', true);

        $signal = $this->signalSlotDispatcher->dispatch(__CLASS__, 'delete', [
            'poll' => $poll,
            'continue' => true,
            'settings' => $this->settings,
            'caller' => $this,
        ]);

        if ($signal['continue']) {
            $this->pollRepository->remove($poll);
            $this->addFlashMessage(TranslateUtility::translate('flash.successfullyDeleted', [$poll->getTitle()]));
            $this->redirect('list');
        }
    }

    public function getContentObjectRow(): ?array
    {
        return $this->configurationManager->getContentObject()->data;
    }

    protected function removeMarkedPollOptions(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): void
    {
        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);

        foreach ($poll->getOptions()->toArray() as $option) {
            // ->toArray() was necessary, because otherwise $poll->getOptions() did not return all items properly.
            if ($option->isMarkToDelete()) {
                $poll->removeOption($option);
                $persistenceManager->remove($option);
            } else {
                $option->getUid() ? $persistenceManager->update($option) : $persistenceManager->add($option);
            }
        }
    }

    private function initializeCurrentUserOrUserIdent(): void
    {
        $this->currentUserIdent = UserIdentUtility::getCurrentUserIdent();
        if (is_numeric($this->currentUserIdent)) {
            $this->currentUser = $this->userRepository->findByUid((int)$this->currentUserIdent);
        }

        $this->settings['_currentUser'] = $this->currentUser;
        $this->settings['_currentUserIdent'] = $this->currentUserIdent;
    }

    private function processPollAndVoteArgumentFromRequest(): void
    {
        // Allow child entities (options)
        if ($this->arguments->hasArgument('poll')) {
            $prop = $this->arguments->getArgument('poll')->getPropertyMappingConfiguration();
            $prop->allowAllProperties();
            $prop->allowProperties('options');
            $prop->forProperty('options.*')->allowProperties('name', 'sorting', 'markToDelete');
            $prop->allowCreationForSubProperty('options.*');
            $prop->allowModificationForSubProperty('options.*');
        }

        // Remove empty option entries and trim non-empty ones
        if ($this->request->hasArgument('poll') && is_array($this->request->getArgument('poll'))) {
            $poll = $this->request->getArgument('poll');
            $pollOptions = $poll['options'];
            if ($pollOptions) {
                $lastSorting = 0;
                foreach ($pollOptions as $index => $pollOption) {
                    if (empty($pollOption['name'])) {
                        unset($poll['options'][$index]); // remove
                    } else {
                        $poll['options'][$index]['name'] = trim($pollOption['name']); // trim

                        if (empty($pollOption['sorting'])) {
                            $lastSorting = $lastSorting * 2;
                            $poll['options'][$index]['sorting'] = (string)$lastSorting;
                        } else {
                            $lastSorting = $pollOption['sorting'];
                        }
                    }
                    if ('' === $pollOption['__identity']) {
                        unset($poll['options'][$index]['__identity']);
                    }
                }
            }
            if (is_array($poll['options'])) {
                $poll['options'] = array_values($poll['options']);
            }
            $this->request->setArgument('poll', $poll);
        }

        if ($this->arguments->hasArgument('vote')) {
            // Disable generic object validator for option_values in polls
            $this->disableGenericObjectValidator('vote', 'optionValues');
            $this->disableGenericObjectValidator('vote', 'poll');
        }

        if ($this->arguments->hasArgument('poll')) {
            // Disable generic object validator for options in polls
            $this->disableGenericObjectValidator('poll', 'options');

            // Set DateTimeConverter format
            $this->arguments->getArgument('poll')->getPropertyMappingConfiguration()
            ->forProperty('settingVotingExpiresDate')
            ->setTypeConverterOption(
                DateTimeConverter::class,
                DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                'Y-m-d'
            );
            $this->arguments->getArgument('poll')->getPropertyMappingConfiguration()
            ->forProperty('settingVotingExpiresTime')
            ->setTypeConverterOption(
                DateTimeConverter::class,
                DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                'H:i'
            );
        }
    }

    /**
     * Only used when current poll has type "schedule".
     */
    private function addCalendarLabelsToSettings(): void
    {
        $poll = null;
        if ($this->arguments->hasArgument('option') && $this->request->hasArgument('option')) {
            $option = $this->request->getArgument('option');
            if (is_numeric($option) && $this->arguments->hasArgument('suggestionDto')) {
                /** @var \FGTCLB\T3oodle\Domain\Model\Option $option */
                $option = $this->optionRepository->findByUid((int)$option);
                $poll = $option->getPoll()->getUid();
            }
        }
        if ($this->arguments->hasArgument('poll') || $poll) {
            $pollType = $this->request->hasArgument('pollType')
                ? $this->request->getArgument('pollType')
                : null;
            if (!$poll && $this->request->hasArgument('poll')) {
                $poll = $this->request->getArgument('poll');
            }
            if (is_numeric($poll) && $this->arguments->hasArgument('suggestionDto')) {
                $pollType = $this->pollRepository->getPollTypeByUid($poll);
            }

            if (!$pollType) {
                if (!is_array($poll)) {
                    return;
                }
                $pollType = $poll['type'];
            }

            if (\FGTCLB\T3oodle\Domain\Model\SchedulePoll::class === $pollType) {
                $this->settings['_calendarLocale'] = json_encode([
                    'weekdays' => [
                        'shorthand' => [
                            LocalizationUtility::translate('weekday.7', 'T3oodle'), // Sun
                            LocalizationUtility::translate('weekday.1', 'T3oodle'), // Mon
                            LocalizationUtility::translate('weekday.2', 'T3oodle'),
                            LocalizationUtility::translate('weekday.3', 'T3oodle'),
                            LocalizationUtility::translate('weekday.4', 'T3oodle'),
                            LocalizationUtility::translate('weekday.5', 'T3oodle'),
                            LocalizationUtility::translate('weekday.6', 'T3oodle'), // Sat
                        ],
                    ],
                    'months' => [
                        'longhand' => [
                            LocalizationUtility::translate('month.1', 'T3oodle'),
                            LocalizationUtility::translate('month.2', 'T3oodle'),
                            LocalizationUtility::translate('month.3', 'T3oodle'),
                            LocalizationUtility::translate('month.4', 'T3oodle'),
                            LocalizationUtility::translate('month.5', 'T3oodle'),
                            LocalizationUtility::translate('month.6', 'T3oodle'),
                            LocalizationUtility::translate('month.7', 'T3oodle'),
                            LocalizationUtility::translate('month.8', 'T3oodle'),
                            LocalizationUtility::translate('month.9', 'T3oodle'),
                            LocalizationUtility::translate('month.10', 'T3oodle'),
                            LocalizationUtility::translate('month.11', 'T3oodle'),
                            LocalizationUtility::translate('month.12', 'T3oodle'),
                        ],
                    ],
                    'weekAbbreviation' => LocalizationUtility::translate('week', 'T3oodle'),
                    'firstDayOfWeek' => (int)LocalizationUtility::translate('firstDayOfWeek', 'T3oodle'),
                    'time_24hr' => (bool)LocalizationUtility::translate('time24hr', 'T3oodle'),
                ]);
            }
        }
    }

    /**
     * @param string $messageBody
     * @param string $messageTitle
     * @param int    $severity
     * @param bool   $storeInSession
     */
    public function addFlashMessage(
        $messageBody,
        $messageTitle = '',
        $severity = AbstractMessage::OK,
        $storeInSession = true
    ): void {
        if ($this->settings['enableFlashMessages']) {
            parent::addFlashMessage($messageBody, $messageTitle, $severity, $storeInSession);
        }
    }
}
