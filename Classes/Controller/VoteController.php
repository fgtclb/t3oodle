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
 * VoteController
 */
class VoteController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

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
     * @param \T3\T3oodle\Domain\Model\Vote $newVote
     * @return void
     */
    public function createAction(\T3\T3oodle\Domain\Model\Vote $newVote)
    {
        $this->addFlashMessage('The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->voteRepository->add($newVote);
        $this->redirect('list');
    }

    /**
     * action edit
     * 
     * @param \T3\T3oodle\Domain\Model\Vote $vote
     * @ignorevalidation $vote
     * @return void
     */
    public function editAction(\T3\T3oodle\Domain\Model\Vote $vote)
    {
        $this->view->assign('vote', $vote);
    }

    /**
     * action update
     * 
     * @param \T3\T3oodle\Domain\Model\Vote $vote
     * @return void
     */
    public function updateAction(\T3\T3oodle\Domain\Model\Vote $vote)
    {
        $this->addFlashMessage('The object was updated. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        $this->voteRepository->update($vote);
        $this->redirect('list');
    }
}
