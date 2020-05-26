<?php
namespace T3\T3oodle\Controller;

use T3\T3oodle\Utility\CookieUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;

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

        $this->processPollArgumentFromRequest();
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
        $polls = $this->pollRepository->findAll();
        $this->view->assign('polls', $polls);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @param \T3\T3oodle\Domain\Model\Vote|null $vote
     * @return void
     * @ignorevalidation $poll
     */
    public function showAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->view->assign('poll', $poll);

        $vote = $this->voteRepository->findByPollAndParticipantIdent($poll, $this->currentUserIdent);
        if (!$vote) {
            $vote = GeneralUtility::makeInstance(\T3\T3oodle\Domain\Model\Vote::class);
            $vote->setParent($poll);
            if ($this->currentUser) {
                $vote->setParticipant($this->currentUser);
            }
        }
        $this->view->assign('vote', $vote);

        $optionTotals = [];
        foreach ($poll->getVotes() as $pollVote) {
            foreach ($pollVote->getOptionValues() as $optionValue) {
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
        $this->view->assign('optionTotals', $optionTotals);

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
        $this->pollPermission->isAllowed($vote->getParent(), 'voting', true);

        if (!$this->currentUser) {
            if (!$this->currentUserIdent) {
                $this->currentUserIdent = base64_encode(uniqid('', true) . uniqid('', true));
            }
            $vote->setParticipantIdent($this->currentUserIdent);
            CookieUtility::set('userIdent', $this->currentUserIdent);
        } else {
            $vote->setParticipant($this->currentUser);
            $vote->setParticipantIdent($this->currentUserIdent);
        }
        $this->voteRepository->add($vote);

        $this->addFlashMessage('Voting saved!', '', AbstractMessage::OK);
        $this->redirect('show', null, null, ['poll' => $vote->getParent()]);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Vote $vote
     * @return void
     */
    public function deleteVoteAction(\T3\T3oodle\Domain\Model\Vote $vote)
    {
        $this->pollPermission->isAllowed($vote, 'voteDeletion', true);
        $name = $vote->getParticipantName();
        $this->voteRepository->remove($vote);
        $this->addFlashMessage('Vote of "' . $name . '" successfully deleted.');
        $this->redirect('show', null, null, ['poll' => $vote->getParent()]);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll|null $poll
     * @return void
     * @ignorevalidation $poll
     */
    public function newAction(\T3\T3oodle\Domain\Model\Poll $poll = null)
    {
        if (!$poll) {
            $poll = GeneralUtility::makeInstance(\T3\T3oodle\Domain\Model\Poll::class);
            if ($this->currentUser) {
                $poll->setAuthor($this->currentUser);
            }
        }
        $this->view->assign('poll', $poll);
        if ($this->request->getOriginalRequest()) {
            $newOptions = $this->request->getOriginalRequest()->getArgument('poll')['options'];
            $this->view->assign('newOptions', $newOptions);
        }
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @return void
     */
    public function createAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
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
        $this->pollRepository->add($poll);

        $this->addFlashMessage('The object was created.', '', AbstractMessage::OK);
        $this->redirect('list');
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @ignorevalidation $poll
     * @return void
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

        $this->removeMarkedPollOptions($poll);
        $this->pollRepository->update($poll);

        $this->addFlashMessage('The object was updated. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', AbstractMessage::WARNING);
        $this->redirect('edit', null, null, ['poll' => $poll]);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @return void
     */
    public function publishAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->pollPermission->isAllowed($poll, 'publish', true);
        $poll->setIsPublished(true);
        $this->pollRepository->update($poll);
        $this->redirect('show', null, null, ['poll' => $poll]);
    }

    /**
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @return void
     */
    public function deleteAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->pollPermission->isAllowed($poll, 'delete', true);
        $this->addFlashMessage('The object was deleted. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', AbstractMessage::WARNING);
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

    private function processPollArgumentFromRequest(): void
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
        if ($this->request->hasArgument('poll')) {
            $poll = $this->request->getArgument('poll');
            $pollOptions = $poll['options'];
            foreach ($pollOptions as $index => $pollOption) {
                if (empty($pollOption['name'])) {
                    unset ($poll['options'][$index]); // remove
                } else {
                    $poll['options'][$index]['name'] = trim($pollOption['name']); // trim
                }
            }
            $this->request->setArgument('poll', $poll);
        }

        if ($this->arguments->hasArgument('vote')) {
            // Disable generic object validator for option_values in polls
            $this->disableGenericObjectValidator('vote', 'optionValues');
            $this->disableGenericObjectValidator('vote', 'parent');
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

    private function initializeCurrentUserOrUserIdent(): void
    {
        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $userAspect = $context->getAspect('frontend.user');
        if ($userAspect->isLoggedIn()) {
            $this->currentUserIdent = $userAspect->get('id');
            $this->currentUser = $this->userRepository->findByUid($this->currentUserIdent);
        } else {
            $this->currentUserIdent = CookieUtility::get('userIdent') ?? '';
        }

        $this->settings['_currentUser'] = $this->currentUser;
        $this->settings['_currentUserIdent'] = $this->currentUserIdent;
    }

    private function disableGenericObjectValidator(string $argumentName, string $propertyName): void
    {
        if ($this->arguments->hasArgument($argumentName)) {
            $validator = $this->arguments->getArgument($argumentName)->getValidator();
            if (method_exists($validator, 'getValidators')) {
                foreach ($validator->getValidators() as $subValidator) {
                    if (method_exists($subValidator, 'getValidators')) {
                        foreach ($subValidator->getValidators() as $subValidatorSub) {
                            if (method_exists($subValidatorSub, 'getPropertyValidators')) {
                                $subValidatorSub->getPropertyValidators($propertyName)->removeAll(
                                    $subValidatorSub->getPropertyValidators($propertyName)
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}
