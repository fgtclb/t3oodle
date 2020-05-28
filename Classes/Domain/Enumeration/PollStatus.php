<?php
namespace T3\T3oodle\Domain\Enumeration;

use TYPO3\CMS\Core\Type\Enumeration;

final class PollStatus extends Enumeration
{
    const __default = self::UNKNOWN;

    const UNKNOWN = 'unknown';
    const DRAFT = 'draft';
    const OPENED = 'opened';
    const CLOSED = 'closed';
    const FINISHED = 'finished';
}
