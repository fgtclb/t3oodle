<?php

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        // Configure plugins
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'T3oodle',
            'Main',
            [
                \FGTCLB\T3oodle\Controller\PollController::class => 'list, show, vote, new, create, edit, update, publish, finish, finishSuggestionMode, ' .
                          'newSuggestion, createSuggestion, editSuggestion, updateSuggestion, deleteSuggestion, ' .
                          'delete, resetVotes, deleteOwnVote',
            ],
            // non-cacheable actions
            [
                \FGTCLB\T3oodle\Controller\PollController::class => 'list, show, vote, create, edit, update, publish, finish, finishSuggestionMode, ' .
                          'createSuggestion, editSuggestion, updateSuggestion, deleteSuggestion, ' .
                          'delete, resetVotes, deleteOwnVote',
            ]
        );

        // Register wizards
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            <<<TS
mod {
    wizards.newContentElement.wizardItems.plugins {
        elements {
            main {
                iconIdentifier = t3oodle-plugin-main
                title = LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_main.name
                description = LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_main.description
                tt_content_defValues {
                    CType = list
                    list_type = t3oodle_main
                }
            }
        }
        show = *
    }
}
TS
        );

        // Register icons
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $iconRegistry->registerIcon(
            't3oodle-plugin-main',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:t3oodle/Resources/Public/Icons/Extension.svg']
        );

        // Register t3oodle's slots
        $dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
        );
        $dispatcher->connect(
            \FGTCLB\T3oodle\Controller\PollController::class,
            'createAfter',
            \FGTCLB\T3oodle\Slots\UpdatePollSlug::class,
            'afterCreate'
        );
        $dispatcher->connect(
            \FGTCLB\T3oodle\Controller\PollController::class,
            'updateBefore',
            \FGTCLB\T3oodle\Slots\UpdatePollSlug::class,
            'beforeUpdate'
        );

        // Register update wizards
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['t3oodleMigrateOneOptionOnlySetting']
            = \FGTCLB\T3oodle\Updates\MigrateOneOptionOnlySetting::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['t3oodleMigrateOldPollTypes']
            = \FGTCLB\T3oodle\Updates\MigrateOldPollTypes::class;

        // Register Extbase Type Converter
        if (\FGTCLB\T3oodle\Utility\Typo3VersionUtility::isTypo3Version()) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
                \FGTCLB\T3oodle\Extbase\TypeConverter\BasePollObjectConverter::class
            );
        } else {
            // Required because of different method signatures
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
                \FGTCLB\T3oodle\Extbase\TypeConverter\BasePollObjectConverterV9::class
            );
        }
    }
);
