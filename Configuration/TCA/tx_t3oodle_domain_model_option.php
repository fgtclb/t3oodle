<?php

declare(strict_types=1);

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
return [
    'ctrl' => [
        'title' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_option',
        'label' => 'name', // TODO: Add userfunc to show amount of votes per option
        'hideTable' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'searchFields' => 'name',
        'iconfile' => 'EXT:t3oodle/Resources/Public/Icons/tx_t3oodle_domain_model_option.gif',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => 'name, --palette--;;creator, poll, ' .
                          '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access',
        ],
    ],
    'palettes' => [
        'creator' => [
            'showitem' => 'creator,--linebreak--,creator_name,creator_mail,creator_ident',
        ],
    ],
    'columns' => [
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
        'sorting' => [
            'exclude' => true,
            'label' => '',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_option.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'creator' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_option.creator',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'maxitems' => 1,
                'readOnly' => true,
                'items' => [
                    ['', 0],
                ],
            ],
        ],
        'creator_name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_option.creator_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'creator_mail' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_option.creator_mail',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,email',
                'readOnly' => true,
            ],
        ],
        'creator_ident' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_option.creator_ident',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'poll' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_option.poll',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_t3oodle_domain_model_poll',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
    ],
];
