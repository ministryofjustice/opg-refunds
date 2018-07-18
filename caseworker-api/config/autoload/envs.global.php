<?php

use Zend\Log\Logger;

return [

    'token_ttl' => 60 * 60 * 1, //  1 hour
    'password_reset_ttl' => 60 * 60 * 1, //  1 hour

    'ingestion' => [
        'enabled' => (bool)getenv('OPG_REFUNDS_CASEWORKER_INGESTION_ENABLED'),
    ],

    'security' => [

        'kms' => [
            'client' => [
                'version' => '2014-11-01',
                'region' => getenv('OPG_REFUNDS_CASEWORKER_API_KMS_REGION') ?: null,
                'endpoint' => getenv('OPG_REFUNDS_CASEWORKER_API_KMS_ENDPOINT') ?: null,
            ],
        ],

        'hash' => [
            // ! < 32 characters.
            'salt' => getenv('OPG_REFUNDS_BANK_HASH_SALT') ?: '',
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
                    PDO::ATTR_PERSISTENT => false
                ]

            ],

        ],

        'cases' => [

            'postgresql' => [

                'adapter' => 'pgsql',
                'host' => getenv('OPG_REFUNDS_DB_CASES_HOSTNAME') ?: null,
                'port' => getenv('OPG_REFUNDS_DB_CASES_PORT') ?: null,
                'dbname' => getenv('OPG_REFUNDS_DB_CASES_NAME') ?: null,
                'username' => getenv('OPG_REFUNDS_DB_CASES_FULL_USERNAME') ?: null,
                'password' => getenv('OPG_REFUNDS_DB_CASES_FULL_PASSWORD') ?: null,
                'options' => [
                    PDO::ATTR_PERSISTENT => false
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
        'enabled' => getenv('OPG_REFUNDS_CASEWORKER_NOTIFY_ENABLED') ?: true,
        'user_id' => getenv('OPG_REFUNDS_CASEWORKER_NOTIFY_USER_ID') ?: 16, //Initial admin user
        'max_notifications' => getenv('OPG_REFUNDS_CASEWORKER_NOTIFY_MAX_NOTIFICATIONS') ?: 4000,
        'timeout' => getenv('OPG_REFUNDS_CASEWORKER_NOTIFY_TIMEOUT') ?: 50,
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

        'sns' => [
            'client' => [
                'version' => '2010-03-31',
                'region' => getenv('OPG_REFUNDS_COMMON_LOGGING_SNS_REGION') ?: null,
                'endpoint' => getenv('OPG_REFUNDS_COMMON_LOGGING_SNS_ENDPOINT') ?: null,
            ],
            'endpoints' => [

                'major' => [
                    'priorities' => [ Logger::EMERG, Logger::ALERT ],
                    'arn' => getenv('OPG_REFUNDS_COMMON_LOGGING_SNS_ENDPOINTS_MAJOR') ?: null,
                ],

                'minor' => [
                    'priorities' => [ Logger::CRIT ],
                    'arn' => getenv('OPG_REFUNDS_COMMON_LOGGING_SNS_ENDPOINTS_MINOR') ?: null,
                ],

                'info' => [
                    'priorities' => [ /* Currently unused */ ],
                    'arn' => getenv('OPG_REFUNDS_COMMON_LOGGING_SNS_ENDPOINTS_INFO') ?: null,
                ],

            ],
        ], // sns

    ], // log

];
