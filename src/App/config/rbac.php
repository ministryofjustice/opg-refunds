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
            'password.change',
            'password.reset',
        ],
        'authenticated-user' => [
            'home',
            'sign.out',
        ],
        User::ROLE_CASEWORKER => [
            'caseworker',
            'process.new.claim',
            'claim',
            'claim.poa',
            'claim.poa.none.found',
            'claim.poa.cancel.none.found',
            'claim.poa.delete',
            'claim.approve',
            'claim.reject',
            'claim.search',
            'claim.change.outcome',
            'claim.reassign',
            'assisted-digital.start',
        ],
        User::ROLE_REPORTING => [
            'reporting',
        ],
        User::ROLE_REFUND => [
            'refund',
            'download',
            'notify',
        ],
        User::ROLE_ADMIN => [
            'user',
            'user.add',
            'user.edit',
            'user.delete',
            'claim.change.outcome',
            'claim.reassign',
        ],
    ],
];
