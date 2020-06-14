<?php

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    't3oodle',
    'Configuration/TypoScript',
    't3oodle Main (required)'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    't3oodle',
    'Configuration/TypoScript/BootstrapStyles',
    't3oodle Custom Bootstrap Styles (optional)'
);
