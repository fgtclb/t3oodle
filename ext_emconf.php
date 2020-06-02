<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 't3oodle',
    'description' => 'Simple poll extension for TYPO3 CMS. t3oodle allows your frontend users to create new polls and vote for existing ones.',
    'category' => 'plugin',
    'version' => '0.1.0',
    'state' => 'alpha',
    'author' => 'Armin Vieweg',
    'author_email' => 'info@v.ieweg.de',
    'author_company' => 'v.ieweg Webentwicklung',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
