<?php

declare(strict_types=1);

use FGTCLB\T3oodle\Domain\Model\BasePoll;
use FGTCLB\T3oodle\Domain\Model\SchedulePoll;
use FGTCLB\T3oodle\Domain\Model\SimplePoll;

return [
    BasePoll::class => [
        'tableName' => 'tx_t3oodle_domain_model_poll',
        'recordType' => BasePoll::class,
        'subclasses' => [
            SimplePoll::class => SimplePoll::class,
            SchedulePoll::class => SchedulePoll::class,
        ],
    ],
    SimplePoll::class => [
        'tableName' => 'tx_t3oodle_domain_model_poll',
        'recordType' => SimplePoll::class,
    ],
    SchedulePoll::class => [
        'tableName' => 'tx_t3oodle_domain_model_poll',
        'recordType' => SchedulePoll::class,
    ],
];
