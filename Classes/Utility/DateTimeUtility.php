<?php declare(strict_types=1);
namespace FGTCLB\T3oodle\Utility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */
final class DateTimeUtility
{
    public static function now(): \DateTime
    {
        return new \DateTime();
    }

    /**
     * @return \DateTime with current time, but set 1970-01-01 as day
     */
    public static function time(): \DateTime
    {
        return self::now()->modify('1970-01-01');
    }

    public static function today(): \DateTime
    {
        return self::now()->modify('midnight');
    }
}
