<?php
namespace T3\T3oodle\Controller;

use T3\T3oodle\Domain\Enumeration\PollType;
use T3\T3oodle\Domain\Enumeration\Visibility;
use T3\T3oodle\Domain\Validator\PollValidator;
use T3\T3oodle\Exception\AccessDeniedException;
use T3\T3oodle\Traits\ControllerValidatorManipulatorTrait;
use T3\T3oodle\Utility\CookieUtility;
use T3\T3oodle\Utility\DateTimeUtility;
use T3\T3oodle\Utility\ScheduleOptionUtility;
use T3\T3oodle\Utility\SlugUtility;
use T3\T3oodle\Utility\TranslateUtility;
use T3\T3oodle\Utility\UserIdentUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
 * PollController
 */
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
     * @var \T3\T3oodle\Domain\Permission\PollPermission
     */
    protected $pollPermission;

    /**
     * @var \T3\T3oodle\Domain\Repository\PollRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $pollRepository;

    /**
     * @var \T3\T3oodle\Domain\Repository\OptionRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $optionRepository;

    /**
     * @var \T3\T3oodle\Domain\Repository\VoteRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $voteRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $userRepository;

    public function initializeAction()
    {
        $this->initializeCurrentUserOrUserIdent();

        $this->pollPermission = GeneralUtility::makeInstance(
            \T3\T3oodle\Domain\Permission\PollPermission::class,
            $this->currentUserIdent
        );

        $this->processPollAndVoteArgumentFromRequest();
        $this->addCalendarLabelsToSettings();
    }

    public function initializeView(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view)
    {
        $view->assign('contentObject', $this->configurationManager->getContentObject()->data);
    }

    protected function getErrorFlashMessage()
    {
        return false;
    }

    /**
     * @return void
     */
    public function listAction()
    {
        $polls = $this->pollRepository->findPolls(
            (bool) $this->settings['list']['draft'],
            (bool) $this->settings['list']['opened'],
            (bool) $this->settings['list']['closed'],
            (bool) $this->settings['list']['finished'],
            (bool) $this->settings['list']['personal']
        );
        $this->view->assign('polls', $polls);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @param \T3\T3oodle\Domain\Model\Vote|null $vote
     * @return void
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function showAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->pollPermission->isAllowed($poll, 'show', true);

        $this->view->assign('poll', $poll);

        $vote = $this->voteRepository->findByPollAndParticipantIdent($poll, $this->currentUserIdent);
        if (!$vote) {
            $vote = GeneralUtility::makeInstance(\T3\T3oodle\Domain\Model\Vote::class);
            $vote->setPoll($poll);
            if ($this->currentUser) {
                $vote->setParticipant($this->currentUser);
            }
        }
        $this->view->assign('vote', $vote);

        if ($this->request->getOriginalRequest()) {
            $newOptionValues = [];
            foreach ($this->request->getOriginalRequest()->getArgument('vote')['optionValues'] as $optionValue) {
                $newOptionValues[$optionValue['option']['__identity']] = $optionValue['value'];
            };
            $this->view->assign('newOptionValues', $newOptionValues);
        }
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Vote $vote
     * @return void
     */
    public function voteAction(\T3\T3oodle\Domain\Model\Vote $vote)
    {
        if (!$this->settings['allowNewVotes']) {
            throw new AccessDeniedException(TranslateUtility::translate('exception.1592142677'), 1592142677);
        }
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
        $this->voteRepository->add($vote);

        $this->addFlashMessage(TranslateUtility::translate('flash.votingSaved'), '', AbstractMessage::OK);
        $this->redirect('show', null, null, ['poll' => $vote->getPoll()]);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Vote $vote
     * @return void
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("vote")
     */
    public function deleteVoteAction(\T3\T3oodle\Domain\Model\Vote $vote)
    {
        if (!$this->settings['allowNewVotes']) {
            throw new AccessDeniedException(TranslateUtility::translate('exception.1592142555'));
        }
        $this->pollPermission->isAllowed($vote, 'voteDeletion', true);
        $name = $vote->getParticipantName();
        $this->voteRepository->remove($vote);
        $this->addFlashMessage(TranslateUtility::translate('flash.voteSuccessfullyDeleted', [$name]));
        $this->redirect('show', null, null, ['poll' => $vote->getPoll()]);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @param int $option uid to finish
     * @return void
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function finishAction(\T3\T3oodle\Domain\Model\Poll $poll, int $option = 0)
    {
        $this->pollPermission->isAllowed($poll, 'finish', true);
        if ($option > 0) {
            // Persist
            /** @var \T3\T3oodle\Domain\Model\Option $option */
            $option = $this->optionRepository->findByUid($option);
            $poll->setFinalOption($option);
            $poll->setFinishDate(DateTimeUtility::now());
            $poll->setIsFinished(true);
            $this->pollRepository->update($poll);
            $this->addFlashMessage(TranslateUtility::translate('flash.successfullyFinished', [$poll->getTitle(), $option->getName()]));
            $this->redirect('show', null, null, ['poll' => $poll]);
        }
        $this->view->assign('poll', $poll);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll|null $poll
     * @param bool $publishDirectly
     * @param string $pollType
     * @return void
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function newAction(
        \T3\T3oodle\Domain\Model\Poll $poll = null,
        bool $publishDirectly = true,
        string $pollType = PollType::SIMPLE
    ) {
        if (!$this->settings['allowNewPolls']) {
            throw new AccessDeniedException(TranslateUtility::translate('exception.1592141715'), 1592141715);
        }
        if (!$poll) {
            $poll = GeneralUtility::makeInstance(\T3\T3oodle\Domain\Model\Poll::class);
            if ($this->currentUser) {
                $poll->setAuthor($this->currentUser);
            }
        }
        $poll->setType($pollType);
        $this->view->assign('poll', $poll);
        if ($this->request->getOriginalRequest()) {
            $newOptions = $this->request->getOriginalRequest()->getArgument('poll')['options'];
            $this->view->assign('newOptions', $newOptions);
        }
        $this->view->assign('publishDirectly', $publishDirectly);
    }

    public function initializeCreateAction()
    {
        $this->disableValidator('poll', PollValidator::class);
        $this->arguments->getArgument('poll')->getValidator()->addValidator(new PollValidator(['action' => 'create']));
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @param bool $publishDirectly
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function createAction(\T3\T3oodle\Domain\Model\Poll $poll, bool $publishDirectly)
    {
        if (!$this->settings['allowNewPolls']) {
            throw new AccessDeniedException(TranslateUtility::translate('exception.1592141715'), 1592141715);
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

        $slugUtility = GeneralUtility::makeInstance(
            SlugUtility::class,
            'tx_t3oodle_domain_model_poll',
            'slug'
        );

        $this->pollRepository->add($poll);

        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        // Create slug and update created entity
        if ($poll->getVisibility() === Visibility::NOT_LISTED) {
            $poll->setSlug($slugUtility->sanitize(uniqid('', true) . $poll->getUid()));
        } else {
            $newSlug = $slugUtility->sanitize($poll->getTitle());
            if ($this->pollRepository->countBySlug($newSlug) > 0) {
                $newSlug .= '-' . $poll->getUid();
            }
            $poll->setSlug($newSlug);
        }
        $this->pollRepository->update($poll);
        $persistenceManager->persistAll();

        $this->addFlashMessage(TranslateUtility::translate('flash.successfullyCreated', [$poll->getTitle()]), '', AbstractMessage::OK);

        if ($publishDirectly) {
            $this->forward('publish', null, null, ['poll' => $poll]);
        }
        $this->redirect('show', null, null, ['poll' => $poll->getUid()]);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @return void
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function publishAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->pollPermission->isAllowed($poll, 'publish', true);
        $poll->setPublishDate(DateTimeUtility::now());
        $poll->setIsPublished(true);
        $this->pollRepository->update($poll);
        $this->addFlashMessage(
            TranslateUtility::translate('flash.successfullyPublished', [$poll->getTitle()]),
            '',
            AbstractMessage::OK
        );
        $this->redirect('show', null, null, ['poll' => $poll]);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @return void
     * @\TYPO3\CMS\Extbase\Annotation\IgnoreValidation("poll")
     */
    public function editAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->pollPermission->isAllowed($poll, 'edit', true);

        $this->view->assign('poll', $poll);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @return void
     */
    public function updateAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->pollPermission->isAllowed($poll, 'edit', true);

        $voteCount = count($poll->getVotes());
        $optionsModified = $poll->areOptionsModified();
        if ($voteCount > 0 && $optionsModified) {
            foreach ($poll->getVotes() as $vote) {
                $this->voteRepository->remove($vote);
            }
        }

        $this->removeMarkedPollOptions($poll);
        $this->pollRepository->update($poll);

        $this->addFlashMessage(TranslateUtility::translate('flash.successfullyUpdated', [$poll->getTitle()]));
        if ($voteCount > 0 && $optionsModified) {
            $this->addFlashMessage(TranslateUtility::translate('flash.noticeRemovedVotes', [$voteCount]), '', AbstractMessage::WARNING);
        }
        $this->redirect('show', null, null, ['poll' => $poll]);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @return void
     */
    public function deleteAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->pollPermission->isAllowed($poll, 'delete', true);
        $this->addFlashMessage(TranslateUtility::translate('flash.successfullyDeleted', [$poll->getTitle()]));
        $this->pollRepository->remove($poll);
        $this->redirect('list');
    }


    protected function removeMarkedPollOptions(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);

        foreach ($poll->getOptions()->toArray() as $option) {
            // ->toArray() was necessary, because otherwise $poll->getOptions() did not return all items properly.
            if ($option->isMarkToDelete()) {
                $poll->removeOption($option);
                $persistenceManager->remove($option);
            }
        }
    }

    private function initializeCurrentUserOrUserIdent(): void
    {
        $this->currentUserIdent = UserIdentUtility::getCurrentUserIdent();
        $this->currentUser = $this->userRepository->findByUid($this->currentUserIdent);

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
            $prop->forProperty('options.*')->allowProperties('name', 'markToDelete');
            $prop->allowCreationForSubProperty('options.*');
            $prop->allowModificationForSubProperty('options.*');
        }

        // Remove empty option entries and trim non-empty ones
        if ($this->request->hasArgument('poll') && is_array($this->request->getArgument('poll'))) {
            $poll = $this->request->getArgument('poll');
            $pollOptions = $poll['options'];
            if ($pollOptions) {
                foreach ($pollOptions as $index => $pollOption) {
                    if (empty($pollOption['name'])) {
                        unset ($poll['options'][$index]); // remove
                    } else {
                        $poll['options'][$index]['name'] = trim($pollOption['name']); // trim
                    }
                }
                if ($poll['type'] === PollType::SCHEDULE) {
                    $pollOptions = [];
                    foreach ($poll['options'] as $option) {
                        // Search for times with single hour digit
                        $option['name'] = preg_replace('/(\D)(\d\:\d\d)/', '${1}0${2}', $option['name']);
                        $pollOptions[] = $option;
                    }
                    // Order options alphabetically
                    $status = usort($pollOptions, function(array $a, array $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    if ($status) {
                        $poll['options'] = $pollOptions;
                    }
                }
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
     * Only used when current poll has type "schedule"
     */
    private function addCalendarLabelsToSettings()
    {
        if ($this->arguments->hasArgument('poll')) {
            $pollType = $this->request->hasArgument('pollType')
                ? $this->request->getArgument('pollType')
                : null;

            if (!$pollType) {
                $poll = $this->request->getArgument('poll');
                if (!is_array($poll) && !is_object($poll)) {
                    return;
                }
                if (is_array($poll)) {
                    $pollType = $poll['type'];
                } else {
                    $pollType = $poll->getType();
                }
            }

            if ($pollType === PollType::SCHEDULE) {
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
                    'firstDayOfWeek' => LocalizationUtility::translate('firstDayOfWeek', 'T3oodle'),
                ]);
            }
        }
    }
}
