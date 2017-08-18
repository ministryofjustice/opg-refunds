<?php

return [

    'session' => [

        'ttl' => 60 * 15, // 15 minutes

        'encryption' => [
            // Keys must be in the format: <ident: int> => <key: 256 bit hex value>
            'keys' => getenv('OPG_REFUNDS_CASEWORKER_FRONT_SESSION_ENCRYPTION_KEYS') ?: null,
        ],

        'dynamodb' => [
            'client' => [
                'version' => '2012-08-10',
                'endpoint' => getenv('OPG_REFUNDS_CASEWORKER_FRONT_SESSION_DYNAMODB_ENDPOINT') ?: null,
                'region' => getenv('OPG_REFUNDS_CASEWORKER_FRONT_SESSION_DYNAMODB_REGION') ?: null,
                'credentials' => ( getenv('AWS_ACCESS_KEY_ID') && getenv('AWS_SECRET_ACCESS_KEY') ) ? [
                    'key'    => getenv('AWS_ACCESS_KEY_ID'),
                    'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
                ] : null,
            ],
            'settings' => [
                'table_name' => getenv('OPG_REFUNDS_CASEWORKER_FRONT_SESSION_DYNAMODB_TABLE') ?: null,
            ],

        ],

    ],

];
