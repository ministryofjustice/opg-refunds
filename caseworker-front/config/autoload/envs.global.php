<?php

use Zend\Log\Logger;

return [

    'api_base_uri' => getenv('API_URL') ?: null,

    'notify' => [
        'api' => [
            'key' => getenv('OPG_REFUNDS_NOTIFY_API_KEY') ?: null,
        ],
    ],

    // Assisted digital
    'ad' => [
        'link' => [
            'domain' => getenv('OPG_REFUNDS_PUBLIC_FRONT_HOSTNAME') ?: null,
            'signature' => [
                'key' => getenv('OPG_REFUNDS_AD_LINK_SIGNATURE_KEY') ?: null,
            ]
        ],
    ],

    'session' => [

        // ini session.* settings...
        'native_settings' => [
            //  The cookie name used in the session
            'name' => 'rs_cw',

            //  Hash settings
            'hash_function' => 'sha512',
            'hash_bits_per_character' => 5,

            //  Only allow the cookie to be sent over https, if we're using HTTPS.
            'cookie_secure' => true,

            //  Prevent cookie from being accessed from JavaScript
            'cookie_httponly' => true,

            //  Don't accept uninitialized session IDs
            'use_strict_mode' => true,

            //  Time before a session can be garbage collected.
            //  (time since the session was last accessed)
            'gc_maxlifetime' => (60 * 60 * 1),  //  1 hour

            //  The probability of GC running is gc_probability/gc_divisor
            'gc_probability' => 0,
            'gc_divisor' => 20,
        ],

        'dynamodb' => [
            'client' => [
                'version' => '2012-08-10',
                'endpoint' => getenv('OPG_REFUNDS_CASEWORKER_FRONT_SESSION_DYNAMODB_ENDPOINT') ?: null,
                'region' => 'eu-west-1',
                'timeout' => 2.0,
            ],
            'settings' => [
                'table_name' => getenv('OPG_REFUNDS_CASEWORKER_FRONT_SESSION_DYNAMODB_TABLE') ?: null,
                'session_lifetime' => (60 * 60 * 1),    //  1 hour
            ],

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
                'timeout' => 2.0,
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
