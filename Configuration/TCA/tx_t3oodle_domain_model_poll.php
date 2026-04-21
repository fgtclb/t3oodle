<?php

declare(strict_types=1);

use FGTCLB\T3oodle\Domain\Enumeration\Visibility;
use FGTCLB\T3oodle\Domain\Model\SchedulePoll;
use FGTCLB\T3oodle\Domain\Model\SimplePoll;
use FGTCLB\T3oodle\Utility\TcaGeneratorUtility;

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
return [
    'ctrl' => [
        'title' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'type' => 'type',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'searchFields' => 'title,description,link,slug,author,author_name,author_mail',
        'iconfile' => 'EXT:t3oodle/Resources/Public/Icons/tx_t3oodle_domain_model_poll.gif',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => 'type, slug',
        ],
        SimplePoll::class => [
            'showitem' => '--palette--;;general, --palette--;;author, title, slug, description, link, options, --palette--;;suggestmode, ' .
                              '--div--;LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.tab.status, --palette--;;publishing, --palette--;;finishing, ' .
                              '--div--;LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.tab.settings,--palette--;Settings;settings, ' .
                              '--div--;LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.tab.votes, votes, ' .
                              '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, ' .
                              'hidden, starttime, endtime, fe_group',
        ],
        SchedulePoll::class => [
            'showitem' => '--palette--;;general, --palette--;;author, title, slug, description, link, options, --palette--;;suggestmode, ' .
                          '--div--;LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.tab.status, --palette--;;publishing, --palette--;;finishing, ' .
                          '--div--;LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.tab.settings,--palette--;Settings;settings, ' .
                          '--div--;LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.tab.votes, votes, ' .
                          '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, ' .
                          'hidden, starttime, endtime, fe_group',
        ],
    ],
    'palettes' => [
        'general' => [
            'showitem' => 'type, visibility',
        ],
        'author' => [
            'showitem' => 'author,--linebreak--,author_name,author_mail,author_ident',
        ],
        'settings' => [
            'showitem' => 'setting_tristate_checkbox, --linebreak--, setting_min_votes_per_participant,setting_max_votes_per_participant, --linebreak--, ' .
                'setting_max_votes_per_option, --linebreak--, setting_secret_participants, setting_secret_votings, setting_super_secret_mode, ' .
                '--linebreak--, setting_voting_expires_date, setting_voting_expires_time',
        ],
        'publishing' => [
            'showitem' => 'is_published, publish_date',
        ],
        'finishing' => [
            'showitem' => 'is_finished, finish_date, final_option',
        ],
        'suggestmode' => [
            'showitem' => 'suggest_mode_enabled, is_suggest_mode_finished',
        ],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        1 => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'fe_group' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 7,
                'maxitems' => 20,
                'items' => [
                    [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
                        'value' => -1,
                    ],
                    [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
                        'value' => -2,
                    ],
                    [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
                        'value' => '--div--',
                    ],
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title',
            ],
        ],
        'crdate' => [
            'exclude' => true,
            'label' => '',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp' => [
            'exclude' => true,
            'label' => '',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'type' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.type.undefined', 'value' => '0'],
                    ['label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.type.simple', 'value' => SimplePoll::class],
                    ['label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.type.schedule', 'value' => SchedulePoll::class],
                ],
                'default' => SimplePoll::class,
                'size' => 1,
                'maxitems' => 1,
                'eval' => 'required',
            ],
        ],
        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'description' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.description',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'eval' => 'trim',
            ],
        ],
        'link' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.link',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'slug' => [
            'exclude' => false,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.slug',
            'config' => [
                'type' => 'slug',
                'size' => 50,
                'generatorOptions' => [
                    'fields' => ['title'],
                    'replacements' => [
                        '/' => '-',
                    ],
                ],
                'fallbackCharacter' => '-',
                'eval' => 'uniqueInPid',
                'default' => '',
            ],
        ],
        'visibility' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.visibility',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => TcaGeneratorUtility::getItemListForEnumeration(
                    Visibility::class
                ),
                'default' => Visibility::LISTED,
                'size' => 1,
                'maxitems' => 1,
                'eval' => 'required',
            ],
        ],
        'author' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.author',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'maxitems' => 1,
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
            ],
        ],
        'author_name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.author_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'author_mail' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.author_mail',
            'config' => [
                'type' => 'email',
                'size' => 30,
            ],
        ],
        'author_ident' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.author_ident',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'options' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.options',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_t3oodle_domain_model_option',
                'foreign_field' => 'poll',
                'foreign_sortby' => 'sorting',
                'maxitems' => 9999,
                'appearance' => [
                    'collapseAll' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'suggest_mode_enabled' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.suggest_mode_enabled',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'is_suggest_mode_finished' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.is_suggest_mode_finished',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'votes' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.votes',
            'displayCond' => 'FIELD:setting_super_secret_mode:!=:1',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_t3oodle_domain_model_vote',
                'foreign_field' => 'poll',
                'maxitems' => 9999,
                'appearance' => [
                    'collapseAll' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                    'enabledControls' => [
                        'new' => false,
                    ],
                ],
            ],

        ],
        'setting_tristate_checkbox' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_tristate_checkbox',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'setting_max_votes_per_option' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_max_votes_per_option',
            'config' => [
                'type' => 'number',
                'default' => '0',
                'size' => 3,
            ],
        ],
        'setting_max_votes_per_participant' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_max_votes_per_participant',
            'config' => [
                'type' => 'number',
                'default' => '0',
                'size' => 3,
            ],
        ],
        'setting_min_votes_per_participant' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_min_votes_per_participant',
            'config' => [
                'type' => 'number',
                'default' => '0',
                'size' => 3,
            ],
        ],
        'setting_secret_participants' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_secret_participants',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'setting_secret_votings' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_secret_votings',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'setting_super_secret_mode' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_super_secret_mode',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'setting_voting_expires_date' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_voting_expires_date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'date,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'setting_voting_expires_time' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_voting_expires_time',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'time,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'is_published' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.is_published',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'publish_date' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.publish_date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'is_finished' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.is_finished',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'finish_date' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.finish_date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'final_option' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.final_option',
            'config' => [
                'default' => 0,
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_t3oodle_domain_model_option',
                'foreign_table_where' => 'AND tx_t3oodle_domain_model_option.poll = ###THIS_UID###',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
    ],
];
