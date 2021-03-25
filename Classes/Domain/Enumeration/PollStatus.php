<?php

namespace FGTCLB\T3oodle\Domain\Enumeration;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Core\Type\Enumeration;

final class PollStatus extends Enumeration
{
    public const UNKNOWN = 'unknown';
    public const DRAFT = 'draft';
    public const OPENED_FOR_SUGGESTIONS = 'openedForSuggestions';
    public const OPENED = 'opened';
    public const CLOSED = 'closed';
    public const FINISHED = 'finished';
}
