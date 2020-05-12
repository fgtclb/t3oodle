<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'T3.T3oodle',
            'Main',
            't3oodle'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('t3oodle', 'Configuration/TypoScript', 't3oodle');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_t3oodle_domain_model_poll', 'EXT:t3oodle/Resources/Private/Language/locallang_csh_tx_t3oodle_domain_model_poll.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_t3oodle_domain_model_poll');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_t3oodle_domain_model_option', 'EXT:t3oodle/Resources/Private/Language/locallang_csh_tx_t3oodle_domain_model_option.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_t3oodle_domain_model_option');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_t3oodle_domain_model_vote', 'EXT:t3oodle/Resources/Private/Language/locallang_csh_tx_t3oodle_domain_model_vote.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_t3oodle_domain_model_vote');

    }
);
