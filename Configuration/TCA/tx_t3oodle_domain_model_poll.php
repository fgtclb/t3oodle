<?php

use T3\T3oodle\Domain\Enumeration\PollType;
use T3\T3oodle\Domain\Enumeration\Visibility;
use T3\T3oodle\Utility\TcaGeneratorUtility;

$ll = T3\T3oodle\Utility\TcaGeneratorUtility::getLocallangClosureFunction(
    'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:'
);

$llCore = T3\T3oodle\Utility\TcaGeneratorUtility::getLocallangClosureFunction(
    'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:'
);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'searchFields' => 'title,description,link,slug,author,author_name,author_mail',
        'iconfile' => 'EXT:t3oodle/Resources/Public/Icons/tx_t3oodle_domain_model_poll.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden, title, type, slug, visibility, is_published, publish_date, author, author_name, author_mail, author_ident, options, fe_group, votes, is_finished, finish_date, final_option',
    ],
    'types' => [
        '1' => ['showitem' => 'title, description, link, type, slug, visibility, is_published, publish_date, is_finished, finish_date, final_option, --palette--;;author, options, --palette--;Settings;settings, votes, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime, fe_group'],
    ],
    'palettes' => [
        'author' => [
            'showitem' => 'author,--linebreak--,author_name,author_mail,author_ident'
        ],
        'settings' => [
            'showitem' => 'setting_tristate_checkbox, --linebreak--, setting_max_votes_per_option, --linebreak--, setting_one_option_only, --linebreak--, setting_secret_participants, setting_secret_votings, --linebreak--, setting_voting_expires_date, setting_voting_expires_time'
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
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true
                    ]
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
                    'allowLanguageSynchronization' => true
                ]
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
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
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
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
                        -1
                    ],
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
                        -2
                    ],
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
                        '--div--'
                    ]
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title',
                'enableMultiSelectFilterTextfield' => true
            ]
        ],
        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'description' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.description',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'eval' => 'trim'
            ],
        ],
        'link' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.link',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'slug' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.slug',
            'config' => [
                'type' => 'slug',
                'size' => 50,
                'generatorOptions' => [
                    'fields' => ['title'],
                    'replacements' => [
                        '/' => '-'
                    ],
                ],
                'fallbackCharacter' => '-',
                'eval' => 'uniqueInPid',
                'default' => ''
            ],
        ],
        'type' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => TcaGeneratorUtility::getItemListForEnumeration(PollType::class),
                'default' => PollType::SIMPLE,
                'size' => 1,
                'maxitems' => 1,
                'eval' => 'required'
            ],
        ],
        'visibility' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.visibility',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => TcaGeneratorUtility::getItemListForEnumeration(Visibility::class),
                'default' => Visibility::LISTED,
                'size' => 1,
                'maxitems' => 1,
                'eval' => 'required'
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
                    ['', 0]
                ],
            ],
        ],
        'author_name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.author_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'author_mail' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.author_mail',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,email'
            ],
        ],
        'author_ident' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.author_ident',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true
            ],
        ],
        'options' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.options',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_t3oodle_domain_model_option',
                'foreign_field' => 'parent',
                'maxitems' => 9999,
                'appearance' => [
                    'collapseAll' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ],
            ],

        ],
        'votes' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_option.votes',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_t3oodle_domain_model_vote',
                'foreign_field' => 'parent',
                'maxitems' => 9999,
                'appearance' => [
                    'collapseAll' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ],
            ],

        ],
        'setting_tristate_checkbox' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_tristate_checkbox',
            'config' => [
                'type' => 'check',
                'default' => 0
            ],
        ],
        'setting_max_votes_per_option' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_max_votes_per_option',
            'config' => [
                'type' => 'input',
                'eval' => 'int',
                'default' => '0',
                'size' => 3
            ],
        ],
        'setting_one_option_only' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_one_option_only',
            'config' => [
                'type' => 'check',
                'default' => 0
            ],
        ],
        'setting_secret_participants' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_secret_participants',
            'config' => [
                'type' => 'check',
                'default' => 0
            ],
        ],
        'setting_secret_votings' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.setting_secret_votings',
            'config' => [
                'type' => 'check',
                'default' => 0
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
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
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
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],
        'is_published' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.is_published',
            'config' => [
                'type' => 'check',
                'default' => 0
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
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],
        'is_finished' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.is_finished',
            'config' => [
                'type' => 'check',
                'default' => 0
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
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],
        'final_option' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_poll.final_option',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_t3oodle_domain_model_option',
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ]
    ],
];
