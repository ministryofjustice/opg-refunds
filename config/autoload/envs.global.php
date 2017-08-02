<?php

return [

    'notify' => [
        'api' => [
            'key' => getenv('OPG_REFUNDS_NOTIFY_API_KEY') ?: null,
        ],
    ],

    'security' => [

        'rsa' => [
            'key' => [
                'public' => getenv('OPG_REFUNDS_PUBLIC_FRONT_BANK_KEY_PUBLIC') ?: null,
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
