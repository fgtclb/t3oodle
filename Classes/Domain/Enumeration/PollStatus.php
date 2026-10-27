<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Domain\Enumeration;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

enum PollStatus: string
{
    case UNKNOWN = 'unknown';
    case DRAFT = 'draft';
    case OPENED_FOR_SUGGESTIONS = 'openedForSuggestions';
    case OPENED = 'opened';
    case CLOSED = 'closed';
    case FINISHED = 'finished';
}
