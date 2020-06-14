<?php
namespace T3\T3oodle\Domain\Enumeration;

use TYPO3\CMS\Core\Type\Enumeration;

final class PollType extends Enumeration
{
    const SIMPLE = 'simple';
    const SCHEDULE = 'schedule';
}
