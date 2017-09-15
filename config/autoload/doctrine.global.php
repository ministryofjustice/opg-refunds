<?php

return [
    'doctrine' => [
        'connection' => [
            'orm_applications' => [
                'params' => [
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                    'host'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME") ?: "localhost",
                    'port'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_PORT") ?: 5432,
                    'user'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_FULL_USERNAME") ?: "applications_full",
                    'password'    => getenv("OPG_REFUNDS_DB_APPLICATIONS_FULL_PASSWORD") ?: "applications_full",
                    'dbname'      => getenv("OPG_REFUNDS_DB_APPLICATIONS_NAME") ?: "applications",
                ],
            ],
            'orm_applications_migration' => [
                'params' => [
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                    'host'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME") ?: "localhost",
                    'port'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_PORT") ?: 5432,
                    'user'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_MIGRATION_USERNAME") ?: "applications_migration",
                    'password'    => getenv("OPG_REFUNDS_DB_APPLICATIONS_MIGRATION_PASSWORD") ?: "applications_migration",
                    'dbname'      => getenv("OPG_REFUNDS_DB_APPLICATIONS_NAME") ?: "applications",
                ],
            ],
            'orm_cases' => [
                'params' => [
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                    'host'        => getenv("OPG_REFUNDS_DB_CASES_HOSTNAME") ?: "localhost",
                    'port'        => getenv("OPG_REFUNDS_DB_CASES_PORT") ?: 5432,
                    'user'        => getenv("OPG_REFUNDS_DB_CASES_FULL_USERNAME") ?: "cases_full",
                    'password'    => getenv("OPG_REFUNDS_DB_CASES_FULL_PASSWORD") ?: "cases_full",
                    'dbname'      => getenv("OPG_REFUNDS_DB_CASES_NAME") ?: "cases",
                ],
            ],
            'orm_cases_migration' => [
                'params' => [
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                    'host'        => getenv("OPG_REFUNDS_DB_CASES_HOSTNAME") ?: "localhost",
                    'port'        => getenv("OPG_REFUNDS_DB_CASES_PORT") ?: 5432,
                    'user'        => getenv("OPG_REFUNDS_DB_CASES_MIGRATION_USERNAME") ?: "cases_migration",
                    'password'    => getenv("OPG_REFUNDS_DB_CASES_MIGRATION_PASSWORD") ?: "cases_migration",
                    'dbname'      => getenv("OPG_REFUNDS_DB_CASES_NAME") ?: "cases",
                ],
            ],
            'orm_sirius' => [
                'params' => [
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                    'host'        => getenv("OPG_REFUNDS_DB_SIRIUS_HOSTNAME") ?: "localhost",
                    'port'        => getenv("OPG_REFUNDS_DB_SIRIUS_PORT") ?: 5432,
                    'user'        => getenv("OPG_REFUNDS_DB_SIRIUS_FULL_USERNAME") ?: "sirius_full",
                    'password'    => getenv("OPG_REFUNDS_DB_SIRIUS_FULL_PASSWORD") ?: "sirius_full",
                    'dbname'      => getenv("OPG_REFUNDS_DB_SIRIUS_NAME") ?: "sirius",
                ],
            ],
            'orm_sirius_migration' => [
                'params' => [
                    'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                    'host'        => getenv("OPG_REFUNDS_DB_SIRIUS_HOSTNAME") ?: "localhost",
                    'port'        => getenv("OPG_REFUNDS_DB_SIRIUS_PORT") ?: 5432,
                    'user'        => getenv("OPG_REFUNDS_DB_SIRIUS_MIGRATION_USERNAME") ?: "sirius_migration",
                    'password'    => getenv("OPG_REFUNDS_DB_SIRIUS_MIGRATION_PASSWORD") ?: "sirius_migration",
                    'dbname'      => getenv("OPG_REFUNDS_DB_SIRIUS_NAME") ?: "sirius",
                ],
            ],
        ],
        'driver' => [
            'orm_applications' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'Applications\Entity' => 'applications_entities',
                ],
            ],
            'orm_applications_migration' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'Applications\Entity' => 'applications_entities',
                ],
            ],
            'applications_entities' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/Applications/src/Entity',
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
            'orm_sirius' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'App\Entity' => 'sirius_entities',
                ],
            ],
            'orm_sirius_migration' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'App\Entity' => 'sirius_entities',
                ],
            ],
            'sirius_entities' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/App/src/Entity/Sirius',
            ],
        ],
    ]
];