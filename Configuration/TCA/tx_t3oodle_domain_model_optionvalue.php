<?php

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

$ll = FGTCLB\T3oodle\Utility\TcaGeneratorUtility::getLocallangClosureFunction(
    'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_optionvalue.'
);

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
        'iconfile' => 'EXT:t3oodle/Resources/Public/Icons/tx_t3oodle_domain_model_optionvalue.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'option, value, vote',
    ],
    'types' => [
        '1' => ['showitem' => 'option, value, vote'],
    ],
    'columns' => [
        'value' => [
            'exclude' => true,
            'label' => $ll('value'),
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$ll('value.0'), 0],
                    [$ll('value.1'), 1],
                    [$ll('value.2'), 2],
                ],
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'option' => [
            'exclude' => true,
            'label' => $ll('option'),
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
            'label' => $ll('vote'),
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
