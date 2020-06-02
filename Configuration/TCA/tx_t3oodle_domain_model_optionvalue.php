<?php
$ll = T3\T3oodle\Utility\TcaGeneratorUtility::getLocallangClosureFunction(
    'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_optionvalue.'
);
return [
    'ctrl' => [
        'title' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_optionvalue',
        'label' => 'option',
        'hideTable' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'readOnly' => true,
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
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
                'type' => 'input',
                'size' => 1,
                'eval' => 'int,required',
                'max' => 2,
                'readOnly' => true,
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
