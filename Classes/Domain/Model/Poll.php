<?php
namespace T3\T3oodle\Domain\Model;


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
 * Poll
 */
class Poll extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * title
     * 
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $title = '';

    /**
     * slug
     * 
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $slug = '';

    /**
     * visibility
     * 
     * @var int
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $visibility = 0;

    /**
     * author
     * 
     * @var string
     */
    protected $author = '';

    /**
     * options
     * 
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\T3oodle\Domain\Model\Option>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $options = null;

    /**
     * Returns the title
     * 
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     * 
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the slug
     * 
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Sets the slug
     * 
     * @param string $slug
     * @return void
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Returns the visibility
     * 
     * @return int $visibility
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Sets the visibility
     * 
     * @param int $visibility
     * @return void
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    /**
     * Returns the author
     * 
     * @return string $author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Sets the author
     * 
     * @param string $author
     * @return void
     */
    public function setAuthor($author)
    {
        $this->author = $author;
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
        $this->options = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Adds a Option
     * 
     * @param \T3\T3oodle\Domain\Model\Option $option
     * @return void
     */
    public function addOption(\T3\T3oodle\Domain\Model\Option $option)
    {
        $this->options->attach($option);
    }

    /**
     * Removes a Option
     * 
     * @param \T3\T3oodle\Domain\Model\Option $optionToRemove The Option to be removed
     * @return void
     */
    public function removeOption(\T3\T3oodle\Domain\Model\Option $optionToRemove)
    {
        $this->options->detach($optionToRemove);
    }

    /**
     * Returns the options
     * 
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\T3oodle\Domain\Model\Option> $options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the options
     * 
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\T3\T3oodle\Domain\Model\Option> $options
     * @return void
     */
    public function setOptions(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $options)
    {
        $this->options = $options;
    }
}
