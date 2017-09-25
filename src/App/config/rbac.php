<?php

use Opg\Refunds\Caseworker\DataModel\Cases\User;

return [
    'roles' => [
        User::ROLE_ADMIN      => [],
        User::ROLE_REFUND     => [],
        User::ROLE_REPORTING  => [],
        User::ROLE_CASEWORKER => [],
        'authenticated-user'        => [
            User::ROLE_ADMIN,
            User::ROLE_REFUND,
            User::ROLE_REPORTING,
            User::ROLE_CASEWORKER,
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
        User::ROLE_CASEWORKER => [
            'caseworker',
            'process.new.claim',
            'claim',
            'claim.poa.sirius',
            'claim.poa.sirius.none.found',
            'claim.poa.sirius.cancel.none.found',
            'claim.poa.meris',
            'claim.poa.meris.none.found',
            'claim.poa.meris.cancel.none.found',
        ],
        User::ROLE_REPORTING => [
            'reporting',
        ],
        User::ROLE_REFUND => [
            'refund',
            'download',
            'csv.download',
        ],
        User::ROLE_ADMIN => [
            'admin',
        ],
    ],
];
