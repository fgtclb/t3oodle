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
 * Vote
 */
class Vote extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * value
     * 
     * @var string
     */
    protected $value = '';

    /**
     * parent
     * 
     * @var \T3\T3oodle\Domain\Model\Option
     */
    protected $parent = null;

    /**
     * Returns the value
     * 
     * @return string $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value
     * 
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Returns the parent
     * 
     * @return \T3\T3oodle\Domain\Model\Option parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the parent
     * 
     * @param \T3\T3oodle\Domain\Model\Option $parent
     * @return void
     */
    public function setParent(\T3\T3oodle\Domain\Model\Option $parent)
    {
        $this->parent = $parent;
    }
}
