<?php

use Zend\Log\Logger;

return [

    'token_ttl' => 60 * 60 * 1, //  1 hour

    'security' => [

        'kms' => [
            'client' => [
                'version' => '2014-11-01',
                'region' => getenv('OPG_REFUNDS_CASEWORKER_API_KMS_REGION') ?: null,
                'endpoint' => getenv('OPG_REFUNDS_CASEWORKER_API_KMS_ENDPOINT') ?: null,
            ],
        ],

        'rsa' => [
            'keys' => [
                'private' => [
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
            'entity' => getenv('OPG_REFUNDS_SSCL_ENTITY') ?: '',
            'cost_centre' => getenv('OPG_REFUNDS_SSCL_COST_CENTRE') ?: '',
            'account' => getenv('OPG_REFUNDS_SSCL_ACCOUNT') ?: '',
            'objective' => getenv('OPG_REFUNDS_SSCL_OBJECTIVE') ?: '00000000',
            'analysis' => getenv('OPG_REFUNDS_SSCL_ANALYSIS') ?: '',
            'completer_id' => getenv('OPG_REFUNDS_SSCL_COMPLETER_ID') ?: '',
            'approver_id' => getenv('OPG_REFUNDS_SSCL_APPROVER_ID') ?: '',
        ],
        'delete_after_historical_refund_dates' => getenv('OPG_REFUNDS_DELETE_AFTER_HISTORICAL_REFUND_DATES') ?: 1
    ],

    'notify' => [
        'api' => [
            'key' => getenv('OPG_REFUNDS_NOTIFY_API_KEY') ?: null,
        ],
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
