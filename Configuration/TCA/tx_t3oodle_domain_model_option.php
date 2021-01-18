<?php

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

$ll = FGTCLB\T3oodle\Utility\TcaGeneratorUtility::getLocallangClosureFunction(
    'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_option.'
);

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
        'iconfile' => 'EXT:t3oodle/Resources/Public/Icons/tx_t3oodle_domain_model_option.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'name, poll',
    ],
    'types' => [
        '1' => [
            'showitem' => 'name, poll, ' .
                          '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access'
        ],
    ],
    'columns' => [
        'name' => [
            'exclude' => true,
            'label' => $ll('name'),
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'poll' => [
            'exclude' => true,
            'label' => $ll('poll'),
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
