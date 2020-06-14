<?php
namespace T3\T3oodle\Domain\Enumeration;

use TYPO3\CMS\Core\Type\Enumeration;

final class Visibility extends Enumeration
{
    const LISTED = 'listed';
    const NOT_LISTED = 'not_listed';
}
