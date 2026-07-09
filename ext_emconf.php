<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 't3oodle',
    'description' => 'Simple poll extension for TYPO3 CMS. t3oodle allows your frontend users to create new polls and vote for existing ones.',
    'category' => 'plugin',
    'version' => '2.0.5',
    'state' => 'beta',
    'author' => 'Armin Vieweg',
    'author_email' => 'info@v.ieweg.de',
    'author_company' => 'FGTCLB',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'fluid_styled_content' => '12.4.0-12.4.99',
            'numbered_pagination' => '2.0.0-2.1.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
