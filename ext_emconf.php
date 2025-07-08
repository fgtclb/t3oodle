<?php

/*  | The t3oodle extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020-2021 Armin Vieweg <info@v.ieweg.de>
 */

// phpcs:disable
$EM_CONF[$_EXTKEY] = [
    'title' => 't3oodle',
    'description' => 'Simple poll extension for TYPO3 CMS. t3oodle allows your frontend users to create new polls and vote for existing ones.',
    'category' => 'plugin',
    'version' => '0.10.0',
    'state' => 'beta',
    'author' => 'Armin Vieweg',
    'author_email' => 'info@v.ieweg.de',
    'author_company' => 'FGTCLB',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
            'fluid_styled_content' => '11.5.0-11.5.99',
            'numbered_pagination' => '2.0.0-2.1.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
