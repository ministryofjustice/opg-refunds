<?php

use Opg\Refunds\Caseworker\DataModel\Cases\User;

return [
    'roles' => [
        User::ROLE_ADMIN            => [],
        User::ROLE_REFUND           => [],
        User::ROLE_REPORTING        => [],
        User::ROLE_CASEWORKER       => [],
        User::ROLE_QUALITY_CHECKING => [],
        'authenticated-user'        => [
            User::ROLE_ADMIN,
            User::ROLE_REFUND,
            User::ROLE_REPORTING,
            User::ROLE_CASEWORKER,
            User::ROLE_QUALITY_CHECKING,
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
            'exception',
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
            'claim.duplicate',
            'claim.search',
            'claim.search.download',
            'claim.confirm.notified',
            'claim.contact.details',
            'phone-claim',
        ],
        User::ROLE_REPORTING => [
            'reporting',
        ],
        User::ROLE_REFUND => [
            'refund',
            'download',
            'notify',
            'verify',
        ],
        User::ROLE_ADMIN => [
            'user',
            'user.add',
            'user.edit',
            'user.delete',
            'claim.change.outcome',
            'claim.reassign',
            'claim.withdraw',
        ],
        User::ROLE_QUALITY_CHECKING => [
            'claim.change.outcome',
        ],
    ],
];
