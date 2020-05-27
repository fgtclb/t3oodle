<?php
namespace T3\T3oodle\Domain\Repository;


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
 * The repository for Polls
 */
class PollRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @var string[] Show unpublished first, then order by publishDate
     */
    protected $defaultOrderings = [
        'isPublished' => 'ASC',
        'publishDate' => 'DESC'
    ];
}
