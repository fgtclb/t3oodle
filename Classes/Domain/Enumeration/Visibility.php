<?php
namespace FGTCLB\T3oodle\Domain\Enumeration;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */
use TYPO3\CMS\Core\Type\Enumeration;

final class Visibility extends Enumeration
{
    const LISTED = 'listed';
    const NOT_LISTED = 'not_listed';
}
