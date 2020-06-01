<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {
        // Configure plugins
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'T3.T3oodle',
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
            'mod {
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
           }'
        );

        // Register icons
		$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        $iconRegistry->registerIcon(
            't3oodle-plugin-main',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:t3oodle/Resources/Public/Icons/user_plugin_main.svg']
        );
    }
);
