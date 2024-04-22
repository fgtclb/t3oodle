<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Utility;

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class Typo3VersionUtility
{
    /**
     * Checks if current TYPO3 version is 10.0.0 or greater (by default).
     *
     * @param string $version
     */
    public static function isTypo3Version($version = '10.0.0'): bool
    {
        /** @var Typo3Version $typo3Version */
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);

        return VersionNumberUtility::convertVersionNumberToInteger($typo3Version->getBranch()) >=
            VersionNumberUtility::convertVersionNumberToInteger($version);
    }
}
