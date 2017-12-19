<?php

use \Zend\Log\Logger;

return [

    'version' => [
        'tag' => getenv('OPG_DOCKER_TAG') ?: 'Unknown',
        'cache' => ( getenv('OPG_DOCKER_TAG') ) ? abs( crc32( getenv('OPG_DOCKER_TAG') ) ) : time(),
    ],

    'stack' => [
        'name' => getenv('OPG_STACKNAME') ?: null,
        'type' => getenv('OPG_REFUNDS_STACK_TYPE') ?: null,
    ],

    'analytics' => [
        'google' => [
            'id' => getenv('OPG_REFUNDS_PUBLIC_FRONT_GOOGLE_ANALYTICS_TRACKING_ID') ?: null,
        ],
    ],

    'refunds' => [
        'processing-time' => '12 weeks'
    ],

    'notify' => [
        'api' => [
            'key' => getenv('OPG_REFUNDS_NOTIFY_API_KEY') ?: null,
        ],
    ],

    'json' => [
        'schema' => [
            'path' => 'config/json-schema.json'
        ]
    ],

    // Assisted digital
    'ad' => [

        'cookie' => [
            'name' => 'ad'
        ],

        'link' => [
            'signature' => [
                'key' => getenv('OPG_REFUNDS_AD_LINK_SIGNATURE_KEY') ?: null,
            ]
        ],

    ],

    'beta' => [

        // Set to false when we move out of beta
        'enabled' => false,

        'cookie' => [
            'name' => 'beta'
        ],

        'link' => [
            'signature' => [
                'key' => getenv('OPG_REFUNDS_PUBLIC_FRONT_BETA_LINK_SIGNATURE_KEY') ?: null,
            ]
        ],

        'dynamodb' => [
            'client' => [
                'version' => '2012-08-10',
                'endpoint' => getenv('OPG_REFUNDS_PUBLIC_BETA_LINK_DYNAMODB_ENDPOINT') ?: null,
                'region' => getenv('OPG_REFUNDS_PUBLIC_BETA_LINK_DYNAMODB_REGION') ?: null,
            ],
            'settings' => [
                'table_name' => getenv('OPG_REFUNDS_PUBLIC_BETA_LINK_DYNAMODB_TABLE') ?: null,
            ],

        ],

    ], // beta

    'security' => [

        'kms' => [
            'client' => [
                'version' => '2014-11-01',
                'endpoint' => getenv('OPG_REFUNDS_PUBLIC_FRONT_KMS_ENCRYPT_ENDPOINT') ?: null,
                'region' => getenv('OPG_REFUNDS_PUBLIC_FRONT_KMS_ENCRYPT_REGION') ?: null,
            ],
            'settings' => [
                'keyId' => getenv('OPG_REFUNDS_PUBLIC_FRONT_KMS_ENCRYPT_KEY_ALIAS') ?: null,
            ],
        ],

        'hash' => [
            // ! < 32 characters.
            'salt' => getenv('OPG_REFUNDS_BANK_HASH_SALT') ?: '',
        ],

    ], // security

    'db' => [
        'postgresql' => [

            'adapter' => 'pgsql',
            'host' => getenv('OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME') ?: null,
            'port' => getenv('OPG_REFUNDS_DB_APPLICATIONS_PORT') ?: null,
            'dbname' => getenv('OPG_REFUNDS_DB_APPLICATIONS_NAME') ?: null,
            'username' => getenv('OPG_REFUNDS_DB_APPLICATIONS_WRITE_USERNAME') ?: null,
            'password' => getenv('OPG_REFUNDS_DB_APPLICATIONS_WRITE_PASSWORD') ?: null,
            'options' => [
                // Warning: RDS and ATTR_PERSISTENT are not friends.
                PDO::ATTR_PERSISTENT => false
            ]

        ],
    ], // db

    'session' => [

        'ttl' => 60 * 60 * 1, // 1 hour

        'encryption' => [
            // Keys must be in the format: <ident: int> => <key: 256 bit hex value>
            'keys' => getenv('OPG_REFUNDS_PUBLIC_FRONT_SESSION_ENCRYPTION_KEYS') ?: null,
        ],

        'dynamodb' => [
            'client' => [
                'version' => '2012-08-10',
                'endpoint' => getenv('OPG_REFUNDS_PUBLIC_FRONT_SESSION_DYNAMODB_ENDPOINT') ?: null,
                'region' => getenv('OPG_REFUNDS_PUBLIC_FRONT_SESSION_DYNAMODB_REGION') ?: null,
            ],
            'settings' => [
                'table_name' => getenv('OPG_REFUNDS_PUBLIC_FRONT_SESSION_DYNAMODB_TABLE') ?: null,
            ],

        ],

    ], // session

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
