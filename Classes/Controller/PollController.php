<?php
namespace T3\T3oodle\Controller;

use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

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
     * pollRepository
     * 
     * @var \T3\T3oodle\Domain\Repository\PollRepository
     * @inject
     */
    protected $pollRepository = null;

    public function initializeView(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view)
    {
        $view->assign('contentObject', $this->configurationManager->getContentObject()->data);
    }

    /**
     * action list
     * 
     * @return void
     */
    public function listAction()
    {
        $polls = $this->pollRepository->findAll();
        $this->view->assign('polls', $polls);
    }

    /**
     * action show
     * 
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @return void
     */
    public function showAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->view->assign('poll', $poll);
    }

    /**
     * action new
     * 
     * @return void
     */
    public function newAction()
    {
    }

    /**
     * action create
     * 
     * @param \T3\T3oodle\Domain\Model\Poll $newPoll
     * @return void
     */
    public function createAction(\T3\T3oodle\Domain\Model\Poll $newPoll)
    {
        $this->managePollOptions($newPoll);
        $this->pollRepository->add($newPoll);

        $this->addFlashMessage('The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->redirect('list');
    }

    /**
     * action edit
     * 
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @ignorevalidation $poll
     * @return void
     */
    public function editAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->view->assign('poll', $poll);
    }

    /**
     * action update
     * 
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @return void
     */
    public function updateAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->managePollOptions($poll);
        $this->pollRepository->update($poll);

        $this->addFlashMessage('The object was updated. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->redirect('edit', null, null, ['poll' => $poll]);
    }

    /**
     * action delete
     * 
     * @param \T3\T3oodle\Domain\Model\Poll $poll
     * @return void
     */
    public function deleteAction(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $this->addFlashMessage('The object was deleted. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->pollRepository->remove($poll);
        $this->redirect('list');
    }


    protected function managePollOptions(\T3\T3oodle\Domain\Model\Poll $poll)
    {
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);

        // Remove options
        foreach ($poll->getOptions()->toArray() as $option) {
            // TODO: ->toArray() was necessary, because otherwise $poll->getOptions() did not return all items properly.
            if ($option->isMarkToDelete()) {
                $poll->removeOption($option);
                $persistenceManager->remove($option);
            }
        }

        // Add new options
        $newOptions = $this->request->getArgument('newOptions') ?? [];
        foreach ($newOptions as $name) {
            $name = trim($name);
            if (!empty($name)) {
                $newOption = new \T3\T3oodle\Domain\Model\Option();
                $newOption->setName($name);
                $poll->addOption($newOption);
            }
        }
    }
}
