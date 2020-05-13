<?php
namespace T3\T3oodle\Domain\Enumeration;

use TYPO3\CMS\Core\Type\Enumeration;

final class PollType extends Enumeration
{
    const __default = self::SIMPLE;

    const SIMPLE = 'simple';
    const APPOINTMENT = 'appointment';
}
