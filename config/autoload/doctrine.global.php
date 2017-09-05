<?php

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                    'host'        => getenv("OPG_REFUNDS_DB_CASES_HOSTNAME") ? getenv("OPG_REFUNDS_DB_CASES_HOSTNAME") : "localhost",
                    'port'        => getenv("OPG_REFUNDS_DB_CASES_PORT") ? intval(getenv("OPG_REFUNDS_DB_CASES_PORT")) : 5432,
                    'user'        => getenv("OPG_REFUNDS_DB_CASES_FULL_USERNAME") ? getenv("OPG_REFUNDS_DB_CASES_FULL_USERNAME") : "cases_full",
                    'password'    => getenv("OPG_REFUNDS_DB_CASES_FULL_PASSWORD") ? getenv("OPG_REFUNDS_DB_CASES_FULL_PASSWORD") : "cases_full",
                    'dbname'      => getenv("OPG_REFUNDS_DB_CASES_NAME") ? getenv("OPG_REFUNDS_DB_CASES_NAME") : "cases",
                ],
            ],
            'orm_migrations' => [
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
            'orm_default' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'App\Entity' => 'refund_entities',
                ],
            ],
            'orm_migrations' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'App\Entity' => 'refund_entities',
                ],
            ],
            'refund_entities' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/App/src/Entity',
            ],
        ],
        'migrations_configuration' => [
            'orm_migrations' => [
                'directory' => 'migrations2',
                'table' => 'doctrine_migrations',
            ],
        ]
    ]
];