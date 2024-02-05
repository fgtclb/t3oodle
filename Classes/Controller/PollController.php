<?php

namespace FGTCLB\T3oodle\Controller;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use FGTCLB\T3oodle\Domain\Validator\CustomPollValidator;
use FGTCLB\T3oodle\Event\CreateAfterEvent;
use FGTCLB\T3oodle\Event\CreateBeforeEvent;
use FGTCLB\T3oodle\Event\CreateSuggestionAfterEvent;
use FGTCLB\T3oodle\Event\CreateSuggestionBeforeEvent;
use FGTCLB\T3oodle\Event\DeleteOwnVoteEvent;
use FGTCLB\T3oodle\Event\DeletePollEvent;
use FGTCLB\T3oodle\Event\DeleteSuggestionEvent;
use FGTCLB\T3oodle\Event\EditPollEvent;
use FGTCLB\T3oodle\Event\EditSuggestionEvent;
use FGTCLB\T3oodle\Event\FinishPollEvent;
use FGTCLB\T3oodle\Event\FinishSuggestionModeEvent;
use FGTCLB\T3oodle\Event\NewPollEvent;
use FGTCLB\T3oodle\Event\NewSuggestionEvent;
use FGTCLB\T3oodle\Event\PublishPollEvent;
use FGTCLB\T3oodle\Event\ResetVotesEvent;
use FGTCLB\T3oodle\Event\ShowPollEvent;
use FGTCLB\T3oodle\Event\ListPollEvent;
use FGTCLB\T3oodle\Event\UpdateAfterEvent;
use FGTCLB\T3oodle\Event\UpdateBeforeEvent;
use FGTCLB\T3oodle\Event\UpdateSuggestionAfterEvent;
use FGTCLB\T3oodle\Event\UpdateSuggestionBeforeEvent;
use FGTCLB\T3oodle\Event\VotePollEvent;
use FGTCLB\T3oodle\Event\ShowFinishEvent;
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
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use GeorgRinger\NumberedPagination\NumberedPagination;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

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
     */
    protected $pollRepository;

    /**
     * @var \FGTCLB\T3oodle\Domain\Repository\OptionRepository
     */
    protected $optionRepository;

    /**
     * @var \FGTCLB\T3oodle\Domain\Repository\VoteRepository
     */
    protected $voteRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
     */
    protected $userRepository;

    protected PersistenceManagerInterface $persistenceManager;

    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

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

    public function listAction(): \Psr\Http\Message\ResponseInterface
    {

        $this->pollRepository->setControllerSettings($this->settings);
        $polls = $this->pollRepository->findPolls(
            (bool)$this->settings['list']['draft'],
            (bool)$this->settings['list']['finished'],
            (bool)$this->settings['list']['personal']
        );

        $itemsPerPage = $this->settings['list']['itemsPerPage'];
        $maximumLinks = 5;

        $currentPage = $this->request->hasArgument('currentPage') ? (int)$this->request->getArgument('currentPage') : 1;
        $paginator = new QueryResultPaginator($polls, $currentPage, $itemsPerPage);
        $pagination = new NumberedPagination($paginator, $maximumLinks);

        $event = new ListPollEvent($polls, $this->settings, $this->view, $this);
        $this->eventDispatcher->dispatch($event);

        $this->view->assignMultiple([
            'polls' => $polls,
            'paginator' => $paginator,
            'pagination' => $pagination,
        ]);
        return $this->htmlResponse();
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function showAction(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): \Psr\Http\Message\ResponseInterface
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

        $event = new ShowPollEvent($poll, $vote, $this->view, $newOptionValues, $this->settings, $this);
        $this->eventDispatcher->dispatch($event);

        $this->view->assign('poll', $poll);
        $this->view->assign('vote', $vote);

        if (!empty($newOptionValues)) {
            $this->view->assign('newOptionValues', $event->getNewOptionValues());
        }

        if ($this->pollPermission->isAllowed($poll, 'suggestNewOptions')) {
            $this->view->assign(
                'mySuggestions',
                $this->optionRepository->findByPollAndCreatorIdent($poll, $this->currentUserIdent) ?? []
            );
        }
        return $this->htmlResponse();
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

        $votePollEvent = new VotePollEvent($vote, !$vote->getUid(), $this->settings, true, $this);
        $this->eventDispatcher->dispatch($votePollEvent);

        if ($votePollEvent->shouldContinue()) {
            $this->voteRepository->add($vote);
            $this->addFlashMessage(
                TranslateUtility::translate($votePollEvent->getIsNew() ? 'flash.votingSaved' : 'flash.votingUpdated'),
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

        $resetVotesEvent = new ResetVotesEvent($poll, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($resetVotesEvent);

        if ($resetVotesEvent->getContinue()) {
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

        $deleteOwnVoteEvent = new DeleteOwnVoteEvent($vote, $vote->getParticipantName(), true, $this->settings, $this);
        $this->eventDispatcher->dispatch($deleteOwnVoteEvent);

        if ($deleteOwnVoteEvent->getContinue()) {
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

            $finishPollEvent = new FinishPollEvent($poll, $option, true, $this->settings, $this->view, $this);
            $this->eventDispatcher->dispatch($finishPollEvent);

            if ($finishPollEvent->getContinue()) {
                $this->pollRepository->update($poll);
                $this->addFlashMessage(
                    TranslateUtility::translate('flash.successfullyFinished', [$poll->getTitle(), $option->getName()])
                );
                $this->redirect('show', null, null, ['poll' => $poll]);
            }
        } else {
            // Display options to choose final one
            $showFinishEvent = new ShowFinishEvent($poll, $this->settings, $this->view, $this);
            $this->eventDispatcher->dispatch($showFinishEvent);
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

        $finishSuggestionModeEvent = new FinishSuggestionModeEvent($poll, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($finishSuggestionModeEvent);

        if ($finishSuggestionModeEvent->getContinue()) {
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
    ): \Psr\Http\Message\ResponseInterface {
        $this->pollPermission->isAllowed($poll, 'suggestNewOptions', true);

        if (!$suggestionDto) {
            $suggestionDto = GeneralUtility::makeInstance(\FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto::class, $poll);
        }
        if ($this->currentUser) {
            $suggestionDto->setCreator($this->currentUser);
        }

        $newSuggestionEvent = new NewSuggestionEvent($poll, $suggestionDto, $this->settings, $this->view, $this);
        $this->eventDispatcher->dispatch($newSuggestionEvent);

        $this->view->assign('suggestionDto', $suggestionDto);
        return $this->htmlResponse();
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

        $createSuggestionBeforeEvent = new CreateSuggestionBeforeEvent($suggestionDto, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($createSuggestionBeforeEvent);

        if ($createSuggestionBeforeEvent->getContinue()) {
            $this->optionRepository->add($newSuggestedOption);

            if ($suggestionDto->getPoll()->isSchedulePoll()) {
                $this->optionRepository->updateSortingOfOptionsByDateTime($suggestionDto->getPoll());
            }

            $this->persistenceManager->persistAll();

            $createSuggestionAfterEvent = new CreateSuggestionAfterEvent($suggestionDto, true, $this->settings, $this);
            $this->eventDispatcher->dispatch($createSuggestionAfterEvent);

            if ($createSuggestionAfterEvent->getContinue()) {
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
    ): \Psr\Http\Message\ResponseInterface {
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

        $editSuggestionEvent = new EditSuggestionEvent($option, $suggestionDto, $this->settings, $this->view, $this);
        $this->eventDispatcher->dispatch($editSuggestionEvent);

        $this->view->assign('suggestionDto', $suggestionDto);
        $this->view->assign('option', $option);
        return $this->htmlResponse();
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

        $updateSuggestionBeforeEvent = new UpdateSuggestionBeforeEvent($suggestionDto, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($updateSuggestionBeforeEvent);

        if ($updateSuggestionBeforeEvent->getContinue()) {
            $this->optionRepository->update($option);

            if ($suggestionDto->getPoll()->isSchedulePoll()) {
                $this->optionRepository->updateSortingOfOptionsByDateTime($suggestionDto->getPoll());
                $this->pollRepository->update($suggestionDto->getPoll());
            }

            $this->persistenceManager->persistAll();

            $updateSuggestionAfterEvent = new UpdateSuggestionAfterEvent($suggestionDto, true, $this->settings, $this);
            $this->eventDispatcher->dispatch($updateSuggestionAfterEvent);

            if ($updateSuggestionAfterEvent->getContinue()) {
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

        $deleteSuggestionEvent = new DeleteSuggestionEvent($option, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($deleteSuggestionEvent);


        if ($deleteSuggestionEvent->getContinue()) {
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
    ): \Psr\Http\Message\ResponseInterface {
        if (!$poll) {
            $poll = GeneralUtility::makeInstance($pollType);
            if ($this->currentUser) {
                $poll->setAuthor($this->currentUser);
            }
        }

        if ($poll->isSimplePoll()) {
            $this->pollPermission->isAllowed($poll, 'newSimplePoll', true);
        } else {
            $this->pollPermission->isAllowed($poll, 'newSchedulePoll', true);
        }

        $newOptions = [];
        /** @var Request|null $originalRequest */
        $originalRequest = $this->request->getOriginalRequest();
        if ($originalRequest) {
            $newOptions = $this->request->getOriginalRequest()->getArgument('poll')['options'] ?? [];
        }

        $newPollEvent = new NewPollEvent($poll, $publishDirectly, $newOptions, $this->settings, $this->view, $this);
        $this->eventDispatcher->dispatch($newPollEvent);

        $this->view->assign('poll', $poll);
        $this->view->assign('publishDirectly', $newPollEvent->getPublishDirectly());
        if (!empty($newPollEvent->getNewOptions())) {
            $this->view->assign('newOptions', $newPollEvent->getNewOptions());
        }
        return $this->htmlResponse();
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
    ) {
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
        $createBeforeEvent = new CreateBeforeEvent($poll, $publishDirectly, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($createBeforeEvent);

        if ($createBeforeEvent->getContinue()) {
            $this->pollRepository->add($poll);

            $this->persistenceManager->persistAll();

            $createAfterEvent = new CreateAfterEvent($poll, $publishDirectly, true, $this->settings, $this);
            $this->eventDispatcher->dispatch($createAfterEvent);

            if ($createAfterEvent->getContinue()) {
                $this->addFlashMessage(
                    TranslateUtility::translate('flash.successfullyCreated', [$poll->getTitle()]),
                    '',
                    AbstractMessage::OK
                );
                if ($publishDirectly) {
                    return (new \TYPO3\CMS\Extbase\Http\ForwardResponse('publish'))->withArguments(['poll' => $poll]);
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

        $publishPollEvent = new PublishPollEvent($poll, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($publishPollEvent);

        if ($publishPollEvent->getContinue()) {
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
    public function editAction(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): \Psr\Http\Message\ResponseInterface
    {
        $this->pollPermission->isAllowed($poll, 'edit', true);

        $editPollEvent = new EditPollEvent($poll, $this->settings, $this->view, $this);
        $this->eventDispatcher->dispatch($editPollEvent);

        $this->view->assign('poll', $poll);
        return $this->htmlResponse();
    }

    /**
     * @TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\CustomPollValidator", param="poll")
     */
    public function updateAction(\FGTCLB\T3oodle\Domain\Model\BasePoll $poll): void
    {
        $this->pollPermission->isAllowed($poll, 'edit', true);

        $voteCount = count($poll->getVotes());
        $optionsModified = $poll->areOptionsModified();

        $updateBeforeEvent = new UpdateBeforeEvent($poll, $voteCount, $optionsModified, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($updateBeforeEvent);

        if ($updateBeforeEvent->getContinue()) {
            if ($voteCount > 0 && $optionsModified) {
                foreach ($poll->getVotes() as $vote) {
                    $this->voteRepository->remove($vote);
                }
            }
            $this->removeMarkedPollOptions($poll);
            $this->pollRepository->update($poll);

            $this->persistenceManager->persistAll();

            $updateAfterEvent = new UpdateAfterEvent($poll, $voteCount, $optionsModified, true, $this->settings, $this);
            $this->eventDispatcher->dispatch($updateAfterEvent);

            if ($updateAfterEvent->getContinue()) {
                $this->addFlashMessage(TranslateUtility::translate('flash.successfullyUpdated', [$poll->getTitle()]));
                if ($updateAfterEvent->getVoteCount() > 0 && $updateAfterEvent->getAreOptionsModified()) {
                    $this->addFlashMessage(
                        TranslateUtility::translate('flash.noticeRemovedVotes', [$updateAfterEvent->getVoteCount()]),
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

        $deletePollEvent = new DeletePollEvent($poll, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($deletePollEvent);

        if ($deletePollEvent->getContinue()) {
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
        foreach ($poll->getOptions()->toArray() as $option) {
            // ->toArray() was necessary, because otherwise $poll->getOptions() did not return all items properly.
            if ($option->isMarkToDelete()) {
                $poll->removeOption($option);
                $this->persistenceManager->remove($option);
            } else {
                $option->getUid() ? $this->persistenceManager->update($option) : $this->persistenceManager->add($option);
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
            $pollOptions = $poll['options'] ?? [];
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
                    $__identity = $pollOption['__identity'] ?? '';
                    if ('' === $__identity) {
                        unset($poll['options'][$index]['__identity']);
                    }
                }
            }
            if (isset($poll['options']) && is_array($poll['options'])) {
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
                $pollType = $poll['type'] ?? false;
            }

            if (false !== stripos($pollType, 'schedule')) {
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

    public function injectPollRepository(\FGTCLB\T3oodle\Domain\Repository\PollRepository $pollRepository): void
    {
        $this->pollRepository = $pollRepository;
    }

    public function injectOptionRepository(\FGTCLB\T3oodle\Domain\Repository\OptionRepository $optionRepository): void
    {
        $this->optionRepository = $optionRepository;
    }

    public function injectVoteRepository(\FGTCLB\T3oodle\Domain\Repository\VoteRepository $voteRepository): void
    {
        $this->voteRepository = $voteRepository;
    }

    public function injectUserRepository(\TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository $userRepository): void
    {
        $this->userRepository = $userRepository;
    }
}
