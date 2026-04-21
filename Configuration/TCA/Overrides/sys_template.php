<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

ExtensionManagementUtility::addStaticFile(
    't3oodle',
    'Configuration/TypoScript',
    't3oodle Main (required)'
);
ExtensionManagementUtility::addStaticFile(
    't3oodle',
    'Configuration/TypoScript/BootstrapStyles',
    't3oodle Custom Bootstrap Styles (optional)'
);
