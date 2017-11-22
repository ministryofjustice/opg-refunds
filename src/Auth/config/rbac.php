<?php

return [
    'roles' => [
        'authenticated-user' => [],
        'guest'              => [
            'authenticated-user'
        ],
    ],
    'permissions' => [
        'guest' => [
            'auth',
            'user.by.token',
            'password.reset',
            'ping',
        ],
        'authenticated-user' => [
            'claim',
            'claim.log',
            'claim.poa',
            'claim.search',
            'user',
            'user.claim',
            'spreadsheet',
            'notify',
            'report'
        ],
    ],
];
