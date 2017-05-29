<?php

return [

    'notify' => [
        'api' => [
            'key' => getenv('OPG_LPA_REFUND_NOTIFY_API_KEY') ?: null,
        ],
    ],

    'security' => [

        'rsa' => [
            'key' => [
                'public' => getenv('OPG_LPA_REFUND_ENCRYPTION_KEY_PUBLIC') ?: null,
            ],
        ],

        'hash' => [
            // ! < 32 characters.
            'salt' => getenv('OPG_LPA_REFUND_ENCRYPTION_HASH_SALT') ?: '',
        ],

    ],

    'db' => [
        'postgresql' => [

            'adapter' => 'pgsql',
            'host' => getenv('API_DATABASE_HOSTNAME') ?: null,
            'port' => getenv('API_DATABASE_PORT') ?: null,
            'dbname' => getenv('API_DATABASE_NAME') ?: null,
            'username' => getenv('API_DATABASE_USERNAME') ?: null,
            'password' => getenv('API_DATABASE_PASSWORD') ?: null,
            'options' => [
                PDO::ATTR_PERSISTENT => true
            ]

        ],
    ],

    'session' => [

        'ttl' => 60 * 60 * 1, // 1 hour

        'encryption' => [
            'key' => getenv('OPG_LPA_REFUND_SESSION_ENCRYPTION_KEY') ?: null,
        ],

        'dynamodb' => [
            'client' => [
                //'endpoint' => 'http://localhost:8000',
                'version' => '2012-08-10',
                'region' => getenv('OPG_LPA_REFUND_SESSION_DYNAMODB_REGION') ?: null,
                'credentials' => ( getenv('AWS_ACCESS_KEY_ID') && getenv('AWS_SECRET_ACCESS_KEY') ) ? [
                    'key'    => getenv('AWS_ACCESS_KEY_ID'),
                    'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
                ] : null,
            ],
            'settings' => [
                'table_name' => getenv('OPG_LPA_REFUND_SESSION_DYNAMODB_TABLE') ?: null,
            ],

        ],

    ],

];