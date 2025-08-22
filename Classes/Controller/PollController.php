<?php

namespace FGTCLB\T3oodle\Controller;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Domain\Model\Dto\SuggestionDto;
use FGTCLB\T3oodle\Domain\Model\Option;
use FGTCLB\T3oodle\Domain\Model\SimplePoll;
use FGTCLB\T3oodle\Domain\Model\Vote;
use FGTCLB\T3oodle\Domain\Permission\PollPermission;
use FGTCLB\T3oodle\Domain\Repository\OptionRepository;
use FGTCLB\T3oodle\Domain\Repository\PollRepository;
use FGTCLB\T3oodle\Domain\Repository\VoteRepository;
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
use FGTCLB\T3oodle\Event\ListPollEvent;
use FGTCLB\T3oodle\Event\NewPollEvent;
use FGTCLB\T3oodle\Event\NewSuggestionEvent;
use FGTCLB\T3oodle\Event\Permission\CreatePollAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\DeletePollAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\DeleteSuggestionAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\EditPollAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\EditSuggestAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\FinishPollAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\FinishSuggestionModeAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\NewOptionsAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\NewPollAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\PublishPollAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\ShowPollAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\SuggestNewOptionsAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\UpdateSuggestionAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\VoteAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\VoteDeleteAllowedEvent;
use FGTCLB\T3oodle\Event\Permission\VoteResetAllowedEvent;
use FGTCLB\T3oodle\Event\PublishPollEvent;
use FGTCLB\T3oodle\Event\ResetVotesEvent;
use FGTCLB\T3oodle\Event\ShowFinishEvent;
use FGTCLB\T3oodle\Event\ShowPollEvent;
use FGTCLB\T3oodle\Event\UpdateAfterEvent;
use FGTCLB\T3oodle\Event\UpdateBeforeEvent;
use FGTCLB\T3oodle\Event\UpdateSuggestionAfterEvent;
use FGTCLB\T3oodle\Event\UpdateSuggestionBeforeEvent;
use FGTCLB\T3oodle\Event\VotePollEvent;
use FGTCLB\T3oodle\Exception\AccessDeniedException;
use FGTCLB\T3oodle\Exception\Permission\CreatePollDeniedException;
use FGTCLB\T3oodle\Exception\Permission\DeletePollDeniedException;
use FGTCLB\T3oodle\Exception\Permission\DeleteSuggestionDeniedException;
use FGTCLB\T3oodle\Exception\Permission\DeleteVoteDeniedException;
use FGTCLB\T3oodle\Exception\Permission\EditPollDeniedException;
use FGTCLB\T3oodle\Exception\Permission\EditSuggestDeniedException;
use FGTCLB\T3oodle\Exception\Permission\FinishPollDeniedException;
use FGTCLB\T3oodle\Exception\Permission\FinishSuggestionModeDeniedException;
use FGTCLB\T3oodle\Exception\Permission\PublishPollDeniedException;
use FGTCLB\T3oodle\Exception\Permission\ShowPollDeniedException;
use FGTCLB\T3oodle\Exception\Permission\SuggestNewOptionsDeniedEception;
use FGTCLB\T3oodle\Exception\Permission\UpdateSuggestionDeniedException;
use FGTCLB\T3oodle\Exception\Permission\VoteResetNotAllowedException;
use FGTCLB\T3oodle\Exception\Permission\VotingDeniedException;
use FGTCLB\T3oodle\Traits\ControllerValidatorManipulatorTrait;
use FGTCLB\T3oodle\Utility\CookieUtility;
use FGTCLB\T3oodle\Utility\DateTimeUtility;
use FGTCLB\T3oodle\Utility\TranslateUtility;
use FGTCLB\T3oodle\Utility\UserIdentUtility;
use GeorgRinger\NumberedPagination\NumberedPagination;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class PollController extends ActionController
{
    use ControllerValidatorManipulatorTrait;

    /**
     * @var mixed
     */
    protected $currentUser;

    /**
     * @var string UID of current frontend user or random string used to identify user by cookie
     */
    protected string $currentUserIdent = '';
    protected PollPermission $pollPermission;
    protected PollRepository $pollRepository;
    protected OptionRepository $optionRepository;
    protected VoteRepository $voteRepository;
    protected FrontendUserRepository $userRepository;
    protected PersistenceManagerInterface $persistenceManager;

    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    public function injectPollRepository(PollRepository $pollRepository): void
    {
        $this->pollRepository = $pollRepository;
    }

    public function injectOptionRepository(OptionRepository $optionRepository): void
    {
        $this->optionRepository = $optionRepository;
    }

    public function injectVoteRepository(VoteRepository $voteRepository): void
    {
        $this->voteRepository = $voteRepository;
    }

    public function injectUserRepository(FrontendUserRepository $userRepository): void
    {
        $this->userRepository = $userRepository;
    }

    public function initializeAction(): void
    {
        $this->initializeCurrentUserOrUserIdent();

        $this->pollPermission = GeneralUtility::makeInstance(
            PollPermission::class,
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

    public function listAction(): ResponseInterface
    {

        $this->pollRepository->setControllerSettings($this->settings);
        // Retrieve polls based on settings
        $polls = $this->pollRepository->findPolls(
            (bool)$this->settings['list']['draft'],
            (bool)$this->settings['list']['finished'],
            (bool)$this->settings['list']['personal']
        );

        $event = new ListPollEvent($polls, $this->settings, $this->view, $this);
        $this->eventDispatcher->dispatch($event);

        $assignedValues = [
            'polls' => $polls,
        ];
        if ($this->settings['list']['itemsPerPage'] > 0) {
            $itemsPerPage = $this->settings['list']['itemsPerPage'];
            $maximumLinks = $this->settings['list']['maximumLinks'];

            $currentPage = $this->request->hasArgument('currentPage') ? (int)$this->request->getArgument('currentPage') : 1;
            $paginator = new QueryResultPaginator($polls, $currentPage, $itemsPerPage);
            $pagination = new NumberedPagination($paginator, $maximumLinks);

            $assignedValues['paginator'] = $paginator;
            $assignedValues['pagination'] = $pagination;
        }

        $this->view->assignMultiple($assignedValues);
        return $this->htmlResponse();

    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     * @throws ShowPollDeniedException
     */
    public function showAction(BasePoll $poll): ResponseInterface
    {
        $isAllowed = $this->pollPermission->isAllowed($poll, 'show');
        $showPollAllowedEvent = new ShowPollAllowedEvent($poll, $isAllowed, $this);
        $this->eventDispatcher->dispatch($showPollAllowedEvent);
        $isAllowed = $showPollAllowedEvent->isAllowed();
        if (!$isAllowed) {
            throw new ShowPollDeniedException(
                TranslateUtility::translate('exception.permission.show'),
                1753700545
            );
        }

        if ($this->request->getOriginalRequestMappingResults()->hasErrors()) {
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
            $vote = new Vote();
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

        $event = new ShowPollEvent(
            $poll,
            $vote,
            $this->view,
            $newOptionValues,
            $this->settings,
            $this
        );
        $this->eventDispatcher->dispatch($event);

        $this->view->assign('poll', $poll);
        $this->view->assign('vote', $vote);

        $newOptionValues = $event->getNewOptionValues();
        if (!empty($newOptionValues)) {
            $this->view->assign('newOptionValues', $event->getNewOptionValues());
        }

        $suggestAllowed = $this->pollPermission->isAllowed($poll, 'suggestNewOptions');
        $suggestAllowedEvent = new NewOptionsAllowedEvent($poll, $suggestAllowed, $this);
        $this->eventDispatcher->dispatch($suggestAllowedEvent);
        $suggestAllowed = $suggestAllowedEvent->isAllowed();
        if ($suggestAllowed) {
            $this->view->assign(
                'mySuggestions',
                $this->optionRepository->findByPollAndCreatorIdent($poll, $this->currentUserIdent) ?? []
            );
        }
        return $this->htmlResponse();
    }

    /**
     * @TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\CustomVoteValidator", param="vote")
     * @throws VotingDeniedException
     */
    public function voteAction(Vote $vote): ResponseInterface
    {
        $voteAllowed = $this->pollPermission->isAllowed($vote->getPoll(), 'voting');
        $voteAllowedEvent = new VoteAllowedEvent($vote, $voteAllowed, $this);
        $this->eventDispatcher->dispatch($voteAllowedEvent);
        if (!$voteAllowedEvent->isAllowed()) {
            throw new VotingDeniedException(
                'Voting not allowed!',
                1753701631
            );
        }

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
            return new RedirectResponse(
                $this
                    ->uriBuilder
                    ->uriFor('show', ['poll' => $vote->getPoll()])
            );
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
        }
        return new RedirectResponse(
            $this
                ->uriBuilder
                ->uriFor('show', ['poll' => $vote->getPoll()])
        );
    }

    /**
     * @throws VoteResetNotAllowedException
     * @todo: add proper return types
     *
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function resetVotesAction(BasePoll $poll): ResponseInterface
    {
        $resetAllowed = $this->pollPermission->isAllowed($poll, 'resetVotes');
        $resetAllowedEvent = new VoteResetAllowedEvent($poll, $resetAllowed, $this);
        $this->eventDispatcher->dispatch($resetAllowedEvent);
        if (!$resetAllowedEvent->isAllowed()) {
            throw new VoteResetNotAllowedException(
                'Reset votes is not allowed!',
                1753713611
            );
        }

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
        }
        return new RedirectResponse(
            $this
                ->uriBuilder
                ->uriFor('show', ['poll' => $poll])
        );
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("vote")
     * @throws DeleteVoteDeniedException
     */
    public function deleteOwnVoteAction(Vote $vote): ResponseInterface
    {
        $deleteVoteAllowed = $this->pollPermission->isAllowed($vote, 'deleteOwnVote');
        $deleteVoteAllowedEvent = new VoteDeleteAllowedEvent($vote, $deleteVoteAllowed, $this);
        $this->eventDispatcher->dispatch($deleteVoteAllowedEvent);
        if (!$deleteVoteAllowedEvent->isAllowed()) {
            throw new DeleteVoteDeniedException(
                'Delete vote is not allowed!',
                1753713961
            );
        }

        $deleteOwnVoteEvent = new DeleteOwnVoteEvent($vote, $vote->getParticipantName(), true, $this->settings, $this);
        $this->eventDispatcher->dispatch($deleteOwnVoteEvent);

        if ($deleteOwnVoteEvent->getContinue()) {
            $this->voteRepository->remove($vote);
            $this->addFlashMessage(
                TranslateUtility::translate('flash.voteSuccessfullyDeleted')
            );
        }
        return new RedirectResponse(
            $this->uriBuilder
                ->uriFor('show', ['poll' => $vote->getPoll()])
        );
    }

    /**
     * @param int $option uid to finish
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     * @throws FinishPollDeniedException
     */
    public function finishAction(BasePoll $poll, int $option = 0): ResponseInterface
    {
        $finishPollAllowed = $this->pollPermission->isAllowed($poll, 'finish');
        $finishPollAllowedEvent = new FinishPollAllowedEvent($poll, $finishPollAllowed, $this);
        $this->eventDispatcher->dispatch($finishPollAllowedEvent);
        if (!$finishPollAllowedEvent->isAllowed()) {
            throw new FinishPollDeniedException(
                'Finish poll is not allowed!',
                1753714157
            );
        }
        if ($option > 0) {
            // Persist final option
            /** @var Option $optionFromDatabase */
            $optionFromDatabase = $this->optionRepository->findByUid($option);
            $poll->setFinalOption($optionFromDatabase);
            $poll->setFinishDate(DateTimeUtility::now());
            $poll->setIsFinished(true);

            $finishPollEvent = new FinishPollEvent($poll, $optionFromDatabase, true, $this->settings, $this->view, $this);
            $this->eventDispatcher->dispatch($finishPollEvent);

            if ($finishPollEvent->getContinue()) {
                $this->pollRepository->update($poll);
                $this->addFlashMessage(
                    TranslateUtility::translate('flash.successfullyFinished', [$poll->getTitle(), $optionFromDatabase->getName()])
                );
                return new RedirectResponse(
                    $this->uriBuilder
                        ->uriFor('show', ['poll' => $poll])
                );
            }
        } else {
            // Display options to choose final one
            $showFinishEvent = new ShowFinishEvent($poll, $this->settings, $this->view, $this);
            $this->eventDispatcher->dispatch($showFinishEvent);
        }
        $this->view->assign('poll', $poll);
        return $this->htmlResponse();
    }

    /**
     * @throws FinishSuggestionModeDeniedException
     * @todo add proper return types
     *
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function finishSuggestionModeAction(BasePoll $poll): ResponseInterface
    {
        $finishSuggestionAllowed = $this->pollPermission->isAllowed($poll, 'finishSuggestionMode');
        $finishSuggestionAllowedEvent = new FinishSuggestionModeAllowedEvent($poll, $finishSuggestionAllowed, $this);
        $this->eventDispatcher->dispatch($finishSuggestionAllowedEvent);
        if (!$finishSuggestionAllowedEvent->isAllowed()) {
            throw new FinishSuggestionModeDeniedException(
                'Finish suggestion mode is not allowed!',
                1753714918
            );
        }

        $poll->setIsSuggestModeFinished(true);

        $finishSuggestionModeEvent = new FinishSuggestionModeEvent($poll, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($finishSuggestionModeEvent);

        if ($finishSuggestionModeEvent->getContinue()) {
            $this->pollRepository->update($poll);
            $this->addFlashMessage(
                TranslateUtility::translate('flash.successfullyFinishedSuggestionMode', [$poll->getTitle()])
            );
        }
        return new RedirectResponse(
            $this->uriBuilder
                ->uriFor('show', ['poll' => $poll])
        );
    }

    /**
     * @throws \FGTCLB\T3oodle\Domain\Permission\AccessDeniedException
     * @throws SuggestNewOptionsDeniedEception
     */
    public function newSuggestionAction(
        BasePoll $poll,
        ?SuggestionDto $suggestionDto = null
    ): ResponseInterface {
        $suggestNewOptionsAllowed = $this->pollPermission->isAllowed($poll, 'suggestNewOptions');
        $suggestNewOptionsAllowedEvent = new SuggestNewOptionsAllowedEvent($poll, $suggestNewOptionsAllowed, $this, $suggestionDto);
        $this->eventDispatcher->dispatch($suggestNewOptionsAllowedEvent);
        if (!$suggestNewOptionsAllowedEvent->isAllowed()) {
            throw new SuggestNewOptionsDeniedEception(
                'Suggest new options is not allowed!',
                1753715367
            );
        }

        if (!$suggestionDto) {
            $suggestionDto = GeneralUtility::makeInstance(SuggestionDto::class, $poll);
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
     * @throws SuggestNewOptionsDeniedEception
     *
     * @\TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\SuggestionDtoValidator", param="suggestionDto")
     */
    public function createSuggestionAction(SuggestionDto $suggestionDto): ResponseInterface
    {
        $suggestNewOptionsAllowed = $this->pollPermission->isAllowed($suggestionDto->getPoll(), 'suggestNewOptions');
        $suggestNewOptionsAllowedEvent = new SuggestNewOptionsAllowedEvent(
            $suggestionDto->getPoll(),
            $suggestNewOptionsAllowed,
            $this,
            $suggestionDto
        );
        $this->eventDispatcher->dispatch($suggestNewOptionsAllowedEvent);
        if (!$suggestNewOptionsAllowedEvent->isAllowed()) {
            throw new SuggestNewOptionsDeniedEception(
                'Suggest new options is not allowed!',
                1753716638
            );
        }

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
            }
        }
        return new RedirectResponse(
            $this->uriBuilder->uriFor('show', ['poll' => $suggestionDto->getPoll()])
        );
    }

    /**
     * @throws \FGTCLB\T3oodle\Domain\Permission\AccessDeniedException
     * @throws AccessDeniedException
     * @throws EditSuggestDeniedException
     */
    public function editSuggestionAction(
        Option $option,
        ?SuggestionDto $suggestionDto = null
    ): ResponseInterface {
        $editSuggestAllowed = $this->pollPermission->isAllowed($option->getPoll(), 'suggestNewOptions', true);
        $editSuggestAllowedEvent = new EditSuggestAllowedEvent(
            $option->getPoll(),
            $editSuggestAllowed,
            $this,
            $suggestionDto
        );
        $this->eventDispatcher->dispatch($editSuggestAllowedEvent);
        if (!$editSuggestAllowedEvent->isAllowed()) {
            throw new EditSuggestDeniedException(
                'Edit suggest option is not allowed!',
                1753716935
            );
        }

        if ($option->getCreatorIdent() !== $this->currentUserIdent) {
            throw new AccessDeniedException(
                'You are trying to update a suggestion, which you did not create!',
                1727789441
            );
        }
        if (!$suggestionDto) {
            /** @var SuggestionDto $suggestionDto */
            $suggestionDto = GeneralUtility::makeInstance(
                SuggestionDto::class,
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
     * @throws UpdateSuggestionDeniedException
     * @throws AccessDeniedException
     */
    public function updateSuggestionAction(
        SuggestionDto $suggestionDto,
        Option $option
    ): ResponseInterface {
        $updateSuggestionAllowed = $this->pollPermission->isAllowed($suggestionDto->getPoll(), 'suggestNewOptions');
        $updateSuggestionAllowedEvent = new UpdateSuggestionAllowedEvent(
            $suggestionDto->getPoll(),
            $updateSuggestionAllowed,
            $this,
            $suggestionDto
        );
        $this->eventDispatcher->dispatch($updateSuggestionAllowedEvent);
        if (!$updateSuggestionAllowedEvent->isAllowed()) {
            throw new UpdateSuggestionDeniedException(
                'Update suggest option is not allowed!',
                1753717074
            );
        }

        if (!$this->currentUser) {
            if (!$this->currentUserIdent) {
                $this->currentUserIdent = base64_encode(uniqid('', true) . uniqid('', true));
            }
            CookieUtility::set('userIdent', $this->currentUserIdent);
        }

        if ($option->getCreatorIdent() !== $this->currentUserIdent) {
            throw new AccessDeniedException(
                'You are trying to update a suggestion, which you did not create!',
                1727789452
            );
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
            }
        }
        return new RedirectResponse(
            $this->uriBuilder->uriFor('show', ['poll' => $suggestionDto->getPoll()])
        );
    }

    /**
     * @throws \FGTCLB\T3oodle\Domain\Permission\AccessDeniedException
     * @throws IllegalObjectTypeException
     * @throws DeleteSuggestionDeniedException
     * @throws AccessDeniedException
     */
    public function deleteSuggestionAction(Option $option): ResponseInterface
    {
        $poll = $option->getPoll();
        $deleteSuggestionAllowed = $this->pollPermission->isAllowed($poll, 'suggestNewOptions', true);
        $deleteSuggestionAllowedEvent = new DeleteSuggestionAllowedEvent(
            $poll,
            $deleteSuggestionAllowed,
            $this
        );
        $this->eventDispatcher->dispatch($deleteSuggestionAllowedEvent);
        if (!$deleteSuggestionAllowedEvent->isAllowed()) {
            throw new DeleteSuggestionDeniedException(
                'Delete suggest option is not allowed!',
                1753717215
            );
        }

        if (!$this->currentUser) {
            if (!$this->currentUserIdent) {
                $this->currentUserIdent = base64_encode(uniqid('', true) . uniqid('', true));
            }
            CookieUtility::set('userIdent', $this->currentUserIdent);
        }

        if ($option->getCreatorIdent() !== $this->currentUserIdent) {
            throw new AccessDeniedException(
                'You are trying to update a suggestion, which you did not create!',
                1727789465
            );
        }

        $deleteSuggestionEvent = new DeleteSuggestionEvent($option, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($deleteSuggestionEvent);

        if ($deleteSuggestionEvent->getContinue()) {
            $this->optionRepository->remove($option);
            $this->addFlashMessage(TranslateUtility::translate('flash.successfullyDeletedSuggestion', [$option->getName()]));
        }
        return new RedirectResponse(
            $this->uriBuilder->uriFor('show', ['poll' => $poll])
        );
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     * @throws \FGTCLB\T3oodle\Domain\Permission\AccessDeniedException
     * @throws NoSuchArgumentException
     */
    public function newAction(
        ?BasePoll $poll = null,
        bool $publishDirectly = true,
        string $pollType = SimplePoll::class
    ): ResponseInterface {
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
        // set allowed to true here, as denied would have thrown an exception.
        $isNewAllowedEvent = new NewPollAllowedEvent($poll, true);
        $this->eventDispatcher->dispatch($isNewAllowedEvent);
        if ($isNewAllowedEvent->isAllowed() === false) {
            throw new \FGTCLB\T3oodle\Domain\Permission\AccessDeniedException(
                'Creating new poll is denied.',
                1755785539
            );
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
     * @throws CreatePollDeniedException
     */
    public function createAction(
        BasePoll $poll,
        bool $publishDirectly,
        bool $acceptTerms = false
    ): ResponseInterface {
        if ($poll->isSimplePoll()) {
            $createAllowed = $this->pollPermission->isAllowed($poll, 'newSimplePoll');
        } else {
            $createAllowed = $this->pollPermission->isAllowed($poll, 'newSchedulePoll');
        }
        $createAllowedEvent = new CreatePollAllowedEvent($poll, $createAllowed, $this);
        $this->eventDispatcher->dispatch($createAllowedEvent);
        if (!$createAllowedEvent->isAllowed()) {
            throw new CreatePollDeniedException(
                'Create poll is not allowed!',
                1753717776
            );
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

        $response = (new ForwardResponse($publishDirectly ? 'publish' : 'show'))
            ->withControllerName('Poll')
            ->withExtensionName('t3oodle')
            ->withArguments(['poll' => $poll]);
        if ($createBeforeEvent->getContinue()) {
            $this->pollRepository->add($poll);

            $this->persistenceManager->persistAll();

            $createAfterEvent = new CreateAfterEvent(
                $poll,
                $publishDirectly,
                true,
                $this->settings,
                $this,
                $response
            );
            $this->eventDispatcher->dispatch($createAfterEvent);

            if ($createAfterEvent->getContinue()) {
                $this->addFlashMessage(
                    TranslateUtility::translate('flash.successfullyCreated', [$poll->getTitle()]),
                    '',
                    AbstractMessage::OK
                );
            }
            $response = $createAfterEvent->getResponse();
        }
        return $response;
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     * @throws \FGTCLB\T3oodle\Domain\Permission\AccessDeniedException
     * @throws PublishPollDeniedException
     */
    public function publishAction(BasePoll $poll): ResponseInterface
    {
        $publishAllowed = $this->pollPermission->isAllowed($poll, 'publish');
        $publishAllowedEvent = new PublishPollAllowedEvent($poll, $publishAllowed, $this);
        $this->eventDispatcher->dispatch($publishAllowedEvent);
        if (!$publishAllowedEvent->isAllowed()) {
            throw new PublishPollDeniedException(
                'Publish poll is not allowed!',
                1753717962
            );
        }
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
            return (new ForwardResponse('show'))
                ->withControllerName('Poll')
                ->withExtensionName('t3oodle')
                ->withArguments(['poll' => $poll]);
        }
        return $this->htmlResponse();
    }

    /**
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     * @throws EditPollDeniedException
     */
    public function editAction(BasePoll $poll): ResponseInterface
    {
        $editAllowed = $this->pollPermission->isAllowed($poll, 'edit');
        $editAllowedEvent = new EditPollAllowedEvent($poll, $editAllowed, $this);
        $this->eventDispatcher->dispatch($editAllowedEvent);
        if (!$editAllowedEvent->isAllowed()) {
            throw new EditPollDeniedException(
                'Edit poll is not allowed!',
                1753718072
            );
        }

        $editPollEvent = new EditPollEvent($poll, $this->settings, $this->view, $this);
        $this->eventDispatcher->dispatch($editPollEvent);

        $this->view->assign('poll', $poll);
        return $this->htmlResponse();
    }

    /**
     * @throws EditPollDeniedException
     * @todo Ensure proper return type is set
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\CustomPollValidator", param="poll")
     */
    public function updateAction(BasePoll $poll): ResponseInterface
    {
        $editAllowed = $this->pollPermission->isAllowed($poll, 'edit');
        $editAllowedEvent = new EditPollAllowedEvent($poll, $editAllowed, $this);
        $this->eventDispatcher->dispatch($editAllowedEvent);
        if (!$editAllowedEvent->isAllowed()) {
            throw new EditPollDeniedException(
                'Edit poll is not allowed!',
                1753718103
            );
        }

        $voteCount = count($poll->getVotes());
        $optionsModified = $poll->areOptionsModified();

        $updateBeforeEvent = new UpdateBeforeEvent($poll, $voteCount, $optionsModified, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($updateBeforeEvent);

        $response = new RedirectResponse(
            $this->uriBuilder->uriFor('edit', ['poll' => $poll])
        );

        if ($updateBeforeEvent->getContinue()) {
            if ($voteCount > 0 && $optionsModified) {
                foreach ($poll->getVotes() as $vote) {
                    $this->voteRepository->remove($vote);
                }
            }
            $this->removeMarkedPollOptions($poll);
            $this->pollRepository->update($poll);

            $this->persistenceManager->persistAll();

            $updateAfterEvent = new UpdateAfterEvent(
                $poll,
                $voteCount,
                $optionsModified,
                true,
                $this->settings,
                $this,
                $response
            );
            $this->eventDispatcher->dispatch($updateAfterEvent);

            $response = $updateAfterEvent->getResponse();

            if ($updateAfterEvent->getContinue()) {
                $this->addFlashMessage(TranslateUtility::translate('flash.successfullyUpdated', [$poll->getTitle()]));
                if ($updateAfterEvent->getVoteCount() > 0 && $updateAfterEvent->getAreOptionsModified()) {
                    $this->addFlashMessage(
                        TranslateUtility::translate('flash.noticeRemovedVotes', [$updateAfterEvent->getVoteCount()]),
                        '',
                        AbstractMessage::WARNING
                    );
                }
            }
        }
        return $response;
    }

    /**
     * @throws DeletePollDeniedException
     * @todo Ensure proper return type is set
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("FGTCLB\T3oodle\Domain\Validator\CustomPollValidator", param="poll")
     */
    public function deleteAction(BasePoll $poll): ResponseInterface
    {
        $deleteAllowed = $this->pollPermission->isAllowed($poll, 'delete', true);
        $deleteAllowedEvent = new DeletePollAllowedEvent($poll, $deleteAllowed, $this);
        $this->eventDispatcher->dispatch($deleteAllowedEvent);
        if (!$deleteAllowedEvent->isAllowed()) {
            throw new DeletePollDeniedException(
                'Delete poll is not allowed!',
                1753718244
            );
        }

        $deletePollEvent = new DeletePollEvent($poll, true, $this->settings, $this);
        $this->eventDispatcher->dispatch($deletePollEvent);

        if ($deletePollEvent->getContinue()) {
            $this->pollRepository->remove($poll);
            $this->addFlashMessage(TranslateUtility::translate('flash.successfullyDeleted', [$poll->getTitle()]));
        }
        return new RedirectResponse(
            $this->uriBuilder->uriFor('list')
        );
    }

    public function getContentObjectRow(): ?array
    {
        return $this->configurationManager->getContentObject()->data;
    }

    protected function removeMarkedPollOptions(BasePoll $poll): void
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
                    if ($__identity === '') {
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
                /** @var Option $option */
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

            if (stripos($pollType, 'schedule') !== false) {
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
