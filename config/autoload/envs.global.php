<?php

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
        'temp_folder' => '/tmp'
    ]

];
