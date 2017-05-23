<?php

return [

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