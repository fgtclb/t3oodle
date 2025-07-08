<?php

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

use FGTCLB\T3oodle\Controller\PollController;
use FGTCLB\T3oodle\Extbase\TypeConverter\BasePollObjectConverter;
use FGTCLB\T3oodle\Updates\MigrateOldPollTypes;
use FGTCLB\T3oodle\Updates\MigrateOneOptionOnlySetting;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

(static function (): void {
    // Configure plugins
    ExtensionUtility::configurePlugin(
        'T3oodle',
        'Main',
        [
            PollController::class => 'list, show, vote, new, create, edit, update, publish, finish, finishSuggestionMode, ' .
                      'newSuggestion, createSuggestion, editSuggestion, updateSuggestion, deleteSuggestion, ' .
                      'delete, resetVotes, deleteOwnVote',
        ],
        // non-cacheable actions
        [
            PollController::class => 'list, show, vote, create, edit, update, publish, finish, finishSuggestionMode, ' .
                      'createSuggestion, editSuggestion, updateSuggestion, deleteSuggestion, ' .
                      'delete, resetVotes, deleteOwnVote',
        ]
    );

    // Register wizards
    ExtensionManagementUtility::addPageTSConfig(
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

    // Register update wizards
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['t3oodleMigrateOneOptionOnlySetting']
        = MigrateOneOptionOnlySetting::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['t3oodleMigrateOldPollTypes']
        = MigrateOldPollTypes::class;

    // Register Extbase Type Converter
    ExtensionUtility::registerTypeConverter(
        BasePollObjectConverter::class
    );
})();
