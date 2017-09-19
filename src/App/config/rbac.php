<?php

use Opg\Refunds\Caseworker\DataModel\Cases\Caseworker;

return [
    'roles' => [
        Caseworker::ROLE_ADMIN      => [],
        Caseworker::ROLE_REFUND     => [],
        Caseworker::ROLE_REPORTING  => [],
        Caseworker::ROLE_CASEWORKER => [],
        'authenticated-user'        => [
            Caseworker::ROLE_ADMIN,
            Caseworker::ROLE_REFUND,
            Caseworker::ROLE_REPORTING,
            Caseworker::ROLE_CASEWORKER,
        ],
        'guest'                     => [
            'authenticated-user'
        ],
    ],
    'permissions' => [
        'guest' => [
            'sign.in',
            'password.request.reset',
        ],
        'authenticated-user' => [
            'home',
            'sign.out',
            'password.set.new',
        ],
        Caseworker::ROLE_CASEWORKER => [
            'caseworker',
        ],
        Caseworker::ROLE_REPORTING => [
            'reporting',
        ],
        Caseworker::ROLE_REFUND => [
            'refund',
            'download',
        ],
        Caseworker::ROLE_ADMIN => [
            'admin',
        ],
    ],
];
