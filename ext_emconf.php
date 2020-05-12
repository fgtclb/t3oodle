<?php

/*  | This extension is made with ❤ for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2020 Armin Vieweg <armin@v.ieweg.de>
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'T3oodle',
    'description' => '',
    'category' => 'plugin',
    'author' => 'Armin Vieweg',
    'author_email' => 'armin@v.ieweg.de',
    'author_company' => '',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => null,
    'modify_tables' => '',
    'clearCacheOnLoad' => false,
    'version' => '0.1.0-dev',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99'
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ]
    ]
];
