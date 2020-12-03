<?php

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <info@v.ieweg.de>
 */

$ll = FGTCLB\T3oodle\Utility\TcaGeneratorUtility::getLocallangClosureFunction(
    'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_vote.'
);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:t3oodle/Resources/Private/Language/locallang_db.xlf:tx_t3oodle_domain_model_vote',
        'label' => 'participant_name',
        'label_alt' => 'participant_ident',
        'hideTable' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'searchFields' => 'participant, participant_name, participant_mail, participant_ident',
        'iconfile' => 'EXT:t3oodle/Resources/Public/Icons/tx_t3oodle_domain_model_vote.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'participant, participant_name, participant_mail, participant_ident, poll',
    ],
    'types' => [
        '1' => ['showitem' => '--palette--;;participant, option_values, poll'],
    ],
    'palettes' => [
        'participant' => [
            'showitem' => 'participant,--linebreak--,participant_name,participant_mail,participant_ident'
        ],
    ],
    'columns' => [
        'participant' => [
            'exclude' => true,
            'label' => $ll('participant'),
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'maxitems' => 1,
                'readOnly' => true,
                'items' => [
                    ['', 0]
                ],
            ],
        ],
        'participant_name' => [
            'exclude' => true,
            'label' => $ll('participant_name'),
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'participant_mail' => [
            'exclude' => true,
            'label' => $ll('participant_mail'),
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,email',
                'readOnly' => true,
            ],
        ],
        'participant_ident' => [
            'exclude' => true,
            'label' => $ll('participant_ident'),
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true
            ],
        ],
        'option_values' => [
            'exclude' => true,
            'label' => $ll('option_values'),
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_t3oodle_domain_model_optionvalue',
                'foreign_field' => 'vote',
                'maxitems' => 9999,
                'readOnly' => true,
                'appearance' => [
                    'collapseAll' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ],
            ],

        ],
        'poll' => [
            'exclude' => true,
            'label' => $ll('poll'),
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_t3oodle_domain_model_poll',
                'minitems' => 1,
                'maxitems' => 1,
                'readOnly' => true,
            ],
        ],
    ],
];
