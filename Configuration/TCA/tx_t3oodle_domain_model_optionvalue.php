<?php

declare(strict_types=1);

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */
return [
    'ctrl' => [
        'title' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_optionvalue',
        'label' => 'option',
        'label_alt' => 'value',
        'label_alt_force' => true,
        'hideTable' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'readOnly' => true,
        'searchFields' => 'name',
        'iconfile' => 'EXT:t3oodle/Resources/Public/Icons/tx_t3oodle_domain_model_optionvalue.gif',
    ],
    'types' => [
        '1' => ['showitem' => 'option, value, vote'],
    ],
    'columns' => [
        'value' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_optionvalue.value',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_optionvalue.value.0', 0],
                    ['LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_optionvalue.value.1', 1],
                    ['LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_optionvalue.value.2', 2],
                ],
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'option' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_optionvalue.option',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_t3oodle_domain_model_option',
                'minitems' => 1,
                'maxitems' => 1,
                'readOnly' => true,
            ],

        ],
        'vote' => [
            'exclude' => true,
            'label' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_optionvalue.vote',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_t3oodle_domain_model_vote',
                'minitems' => 1,
                'maxitems' => 1,
                'readOnly' => true,
            ],
        ],
    ],
];
