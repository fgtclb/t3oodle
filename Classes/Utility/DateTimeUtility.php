<?php declare(strict_types=1);
namespace T3\T3oodle\Utility;

final class DateTimeUtility
{
    public static function now(): \DateTime
    {
        return new \DateTime();
    }

    public static function today(): \DateTime
    {
        return self::now()->modify('midnight');
    }
}
