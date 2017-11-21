<?php

return (getenv('OPG_REFUNDS_STACK_TYPE') !== 'loadtest') ? [] :
[

    'notify' => [
        'api' => [
            'key' => getenv('OPG_REFUNDS_NOTIFY_PHANTOM_API_KEY') ?: null,
        ],
    ],

    'beta' => [
        'enabled' => false,
    ],

];
