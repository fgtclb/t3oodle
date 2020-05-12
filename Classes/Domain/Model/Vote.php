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
     * option
     * 
     * @var \T3\T3oodle\Domain\Model\Option
     */
    protected $option = null;

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
     * Returns the option
     * 
     * @return \T3\T3oodle\Domain\Model\Option $option
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Sets the option
     * 
     * @param \T3\T3oodle\Domain\Model\Option $option
     * @return void
     */
    public function setOption(\T3\T3oodle\Domain\Model\Option $option)
    {
        $this->option = $option;
    }
}
