<?php

return [

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
                'host' => getenv('API_DATABASE_HOSTNAME') ?: null,
                'port' => getenv('API_DATABASE_PORT') ?: null,
                'dbname' => getenv('API_DATABASE_NAME') ?: null,
                'username' => getenv('API_DATABASE_NAME') ?: null,
                'password' => getenv('POSTGRES_PASSWORD') ?: null,
                'options' => [
                    PDO::ATTR_PERSISTENT => true
                ]

            ],

        ],
    ],

];
