<?php
namespace T3\T3oodle\Domain\Enumeration;

use TYPO3\CMS\Core\Type\Enumeration;

final class Visbility extends Enumeration
{
    const __default = self::PUBLIC;

    const PUBLIC = 'public';
    const SECRET = 'secret';
    const PRIVATE = 'private';
}
