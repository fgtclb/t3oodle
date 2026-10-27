<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

$pluginSignature = ExtensionUtility::registerPlugin(
    'T3oodle',
    'Main',
    't3oodle'
);

ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', $pluginSignature, 'after:subheader');
ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:t3oodle/Configuration/FlexForms/Main.xml',
    $pluginSignature
);
