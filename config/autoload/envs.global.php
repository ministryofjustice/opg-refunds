<?php

return [

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

    'beta' => [

        // Set to false when we move out of beta
        'enabled' => true,

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

    ],

    'security' => [

        'rsa' => [
            'keys' => [
                'public' => [
                    'full' => getenv('OPG_REFUNDS_PUBLIC_FRONT_FULL_KEY_PUBLIC') ?: null,
                    'bank' => getenv('OPG_REFUNDS_PUBLIC_FRONT_BANK_KEY_PUBLIC') ?: null,
                ],
            ],
        ],

        'hash' => [
            // ! < 32 characters.
            'salt' => getenv('OPG_REFUNDS_BANK_HASH_SALT') ?: '',
        ],

    ],

    'db' => [
        'postgresql' => [

            'adapter' => 'pgsql',
            'host' => getenv('OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME') ?: null,
            'port' => getenv('OPG_REFUNDS_DB_APPLICATIONS_PORT') ?: null,
            'dbname' => getenv('OPG_REFUNDS_DB_APPLICATIONS_NAME') ?: null,
            'username' => getenv('OPG_REFUNDS_DB_APPLICATIONS_WRITE_USERNAME') ?: null,
            'password' => getenv('OPG_REFUNDS_DB_APPLICATIONS_WRITE_PASSWORD') ?: null,
            'options' => [
                PDO::ATTR_PERSISTENT => true
            ]

        ],
    ],

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

    ],

];
