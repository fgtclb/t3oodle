<?php
namespace T3\T3oodle\Domain\Model;

use T3\T3oodle\Domain\Traits\MarkToDeleteTrait;

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
 * Option
 */
class Option extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    use MarkToDeleteTrait;

    /**
     * name
     * 
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $name = '';

    /**
     * selected
     * 
     * @var bool
     */
    protected $selected = false;

    /**
     * votes
     * 
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\T3oodle\Domain\Model\Vote>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $votes = null;

    /**
     * parent
     * 
     * @var \T3\T3oodle\Domain\Model\Poll
     */
    protected $parent = null;

    /**
     * Returns the name
     * 
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     * 
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the selected
     * 
     * @return bool $selected
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * Sets the selected
     * 
     * @param bool $selected
     * @return void
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;
    }

    /**
     * Returns the boolean state of selected
     * 
     * @return bool
     */
    public function isSelected()
    {
        return $this->selected;
    }

    /**
     * __construct
     */
    public function __construct()
    {

        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     * 
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->votes = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Adds a Vote
     * 
     * @param \T3\T3oodle\Domain\Model\Vote $vote
     * @return void
     */
    public function addVote(\T3\T3oodle\Domain\Model\Vote $vote)
    {
        $vote->setParent($this);
        $this->votes->attach($vote);
    }

    /**
     * Removes a Vote
     * 
     * @param \T3\T3oodle\Domain\Model\Vote $voteToRemove The Vote to be removed
     * @return void
     */
    public function removeVote(\T3\T3oodle\Domain\Model\Vote $voteToRemove)
    {
        $this->votes->detach($voteToRemove);
    }

    /**
     * Returns the votes
     * 
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\T3oodle\Domain\Model\Vote> $votes
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Sets the votes
     * 
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\T3oodle\Domain\Model\Vote> $votes
     * @return void
     */
    public function setVotes(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $votes)
    {
        $this->votes = $votes;
    }

    /**
     * Returns the parent
     * 
     * @return \T3\T3oodle\Domain\Model\Poll parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the parent
     * 
     * @param \T3\T3oodle\Domain\Model\Poll $parent
     * @return void
     */
    public function setParent(\T3\T3oodle\Domain\Model\Poll $parent)
    {
        $this->parent = $parent;
    }
}
