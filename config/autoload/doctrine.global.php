<?php

return [
    'doctrine' => [
        'connection' => [
            'orm_auth' => [
                'params' => [
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                    'host'        => getenv("OPG_REFUNDS_DB_AUTH_HOSTNAME") ? getenv("OPG_REFUNDS_DB_AUTH_HOSTNAME") : "localhost",
                    'port'        => getenv("OPG_REFUNDS_DB_AUTH_PORT") ? intval(getenv("OPG_REFUNDS_DB_AUTH_PORT")) : 5432,
                    'user'        => getenv("OPG_REFUNDS_DB_AUTH_FULL_USERNAME") ? getenv("OPG_REFUNDS_DB_AUTH_FULL_USERNAME") : "auth_full",
                    'password'    => getenv("OPG_REFUNDS_DB_AUTH_FULL_PASSWORD") ? getenv("OPG_REFUNDS_DB_AUTH_FULL_PASSWORD") : "auth_full",
                    'dbname'      => getenv("OPG_REFUNDS_DB_AUTH_NAME") ? getenv("OPG_REFUNDS_DB_AUTH_NAME") : "auth",
                ],
            ],
            'orm_auth_migration' => [
                'params' => [
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                    'host'        => getenv("OPG_REFUNDS_DB_AUTH_HOSTNAME") ? getenv("OPG_REFUNDS_DB_AUTH_HOSTNAME") : "localhost",
                    'port'        => getenv("OPG_REFUNDS_DB_AUTH_PORT") ? intval(getenv("OPG_REFUNDS_DB_AUTH_PORT")) : 5432,
                    'user'        => getenv("OPG_REFUNDS_DB_AUTH_MIGRATE_USERNAME") ? getenv("OPG_REFUNDS_DB_AUTH_MIGRATE_USERNAME") : "auth_migration",
                    'password'    => getenv("OPG_REFUNDS_DB_AUTH_MIGRATE_PASSWORD") ? getenv("OPG_REFUNDS_DB_AUTH_MIGRATE_PASSWORD") : "auth_migration",
                    'dbname'      => getenv("OPG_REFUNDS_DB_AUTH_NAME") ? getenv("OPG_REFUNDS_DB_AUTH_NAME") : "auth",
                ],
            ],
            'orm_cases' => [
                'params' => [
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                    'host'        => getenv("OPG_REFUNDS_DB_CASES_HOSTNAME") ? getenv("OPG_REFUNDS_DB_CASES_HOSTNAME") : "localhost",
                    'port'        => getenv("OPG_REFUNDS_DB_CASES_PORT") ? intval(getenv("OPG_REFUNDS_DB_CASES_PORT")) : 5432,
                    'user'        => getenv("OPG_REFUNDS_DB_CASES_FULL_USERNAME") ? getenv("OPG_REFUNDS_DB_CASES_FULL_USERNAME") : "cases_full",
                    'password'    => getenv("OPG_REFUNDS_DB_CASES_FULL_PASSWORD") ? getenv("OPG_REFUNDS_DB_CASES_FULL_PASSWORD") : "cases_full",
                    'dbname'      => getenv("OPG_REFUNDS_DB_CASES_NAME") ? getenv("OPG_REFUNDS_DB_CASES_NAME") : "cases",
                ],
            ],
            'orm_cases_migration' => [
                'params' => [
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                    'host'        => getenv("OPG_REFUNDS_DB_CASES_HOSTNAME") ? getenv("OPG_REFUNDS_DB_CASES_HOSTNAME") : "localhost",
                    'port'        => getenv("OPG_REFUNDS_DB_CASES_PORT") ? intval(getenv("OPG_REFUNDS_DB_CASES_PORT")) : 5432,
                    'user'        => getenv("OPG_REFUNDS_DB_CASES_MIGRATE_USERNAME") ? getenv("OPG_REFUNDS_DB_CASES_MIGRATE_USERNAME") : "cases_migration",
                    'password'    => getenv("OPG_REFUNDS_DB_CASES_MIGRATE_PASSWORD") ? getenv("OPG_REFUNDS_DB_CASES_MIGRATE_PASSWORD") : "cases_migration",
                    'dbname'      => getenv("OPG_REFUNDS_DB_CASES_NAME") ? getenv("OPG_REFUNDS_DB_CASES_NAME") : "cases",
                ],
            ],
        ],
        'driver' => [
            'orm_auth' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'App\Entity' => 'auth_entities',
                ],
            ],
            'orm_auth_migration' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'App\Entity' => 'auth_entities',
                ],
            ],
            'auth_entities' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/App/src/Entity/Auth',
            ],
            'orm_cases' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'App\Entity' => 'cases_entities',
                ],
            ],
            'orm_cases_migration' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'App\Entity' => 'cases_entities',
                ],
            ],
            'cases_entities' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/App/src/Entity/Cases',
            ],
        ],
    ]
];