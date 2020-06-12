<?php

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'T3.T3oodle',
    'Main',
    't3oodle'
);

$pluginSignature = 't3oodle_main';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:t3oodle/Configuration/FlexForms/Main.xml'
);