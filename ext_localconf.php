<?php

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        // Configure plugins
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'FGTCLB.T3oodle',
            'Main',
            [
                'Poll' => 'list, show, vote, new, create, edit, update, publish, finish, delete, deleteVote',
            ],
            // non-cacheable actions
            [
                'Poll' => 'list, show, vote, create, edit, update, publish, finish, delete, deleteVote',
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
            \T3\T3oodle\Controller\PollController::class,
            'createAfter',
            \T3\T3oodle\Slots\UpdatePollSlug::class,
            'afterCreate'
        );
        $dispatcher->connect(
            \T3\T3oodle\Controller\PollController::class,
            'updateBefore',
            \T3\T3oodle\Slots\UpdatePollSlug::class,
            'beforeUpdate'
        );
    }
);
