<?php
namespace T3\T3oodle\Controller;


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
        $this->addFlashMessage('The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->pollRepository->add($newPoll);
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
        $this->addFlashMessage('The object was updated. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->pollRepository->update($poll);
        $this->redirect('list');
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
}
