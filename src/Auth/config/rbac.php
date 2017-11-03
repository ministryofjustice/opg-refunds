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
            //  Developer routes
            'dev.applications',
            'dev.view-claim-queue',
        ],
    ],
];
