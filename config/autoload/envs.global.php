<?php

use Zend\Log\Logger;

return [

    'token_ttl' => 60 * 60 * 1, //  1 hour

    'notify' => [
        'api' => [
            'key' => getenv('OPG_REFUNDS_NOTIFY_API_KEY') ?: null,
        ],
    ],

    'security' => [

        'rsa' => [
            'keys' => [
                'private' => [
                    'full' => getenv('OPG_REFUNDS_CASEWORKER_API_FULL_KEY_PRIVATE') ?: null,
                    'bank' => getenv('OPG_REFUNDS_CASEWORKER_API_BANK_KEY_PRIVATE') ?: null,
                ],
            ],
        ],

    ],

    'db' => [
        'incoming' => [

            'postgresql' => [

                'adapter' => 'pgsql',
                'host' => getenv('OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME') ?: null,
                'port' => getenv('OPG_REFUNDS_DB_APPLICATIONS_PORT') ?: null,
                'dbname' => getenv('OPG_REFUNDS_DB_APPLICATIONS_NAME') ?: null,
                'username' => getenv('OPG_REFUNDS_DB_APPLICATIONS_FULL_USERNAME') ?: null,
                'password' => getenv('OPG_REFUNDS_DB_APPLICATIONS_FULL_PASSWORD') ?: null,
                'options' => [
                    PDO::ATTR_PERSISTENT => true
                ]

            ],

        ],
    ],

    'spreadsheet' => [
        'source_folder' => __DIR__.'/../../assets',
        'temp_folder' => '/tmp',
        'sscl' => [
            'entity' => getenv('OPG_REFUNDS_SSCL_ENTITY') ?: '0123',
            'cost_centre' => getenv('OPG_REFUNDS_SSCL_COST_CENTRE') ?: '99999999',
            'account' => getenv('OPG_REFUNDS_SSCL_ACCOUNT') ?: '123450000',
            'objective' => getenv('OPG_REFUNDS_SSCL_OBJECTIVE') ?: '0',
            'analysis' => getenv('OPG_REFUNDS_SSCL_ANALYSIS') ?: '12345678',
            'completer_id' => getenv('OPG_REFUNDS_SSCL_ANALYSIS') ?: 'completer@localhost.com',
            'approver_id' => getenv('OPG_REFUNDS_SSCL_ANALYSIS') ?: 'approver@localhost.com',
        ]
    ],


    'log' => [

        'logstash' => [
            'path' => '/var/log/app/application.log',
        ],

        'priorities' => [
            // The priority we class 500 exceptions as
            '500' => Logger::CRIT,
        ],

    ], // log

];
