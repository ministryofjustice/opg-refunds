<?php

return [
    'connection' => [
        'orm_applications' => [
            'params' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                'host'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME") ?: "postgres",
                'port'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_PORT") ?: 5432,
                'user'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_FULL_USERNAME") ?: "applications_full",
                'password'    => getenv("OPG_REFUNDS_DB_APPLICATIONS_FULL_PASSWORD") ?: "applications_full",
                'dbname'      => getenv("OPG_REFUNDS_DB_APPLICATIONS_NAME") ?: "applications",
            ],
        ],
        'orm_applications_migration' => [
            'params' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                'host'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME") ?: "postgres",
                'port'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_PORT") ?: 5432,
                'user'        => getenv("OPG_REFUNDS_DB_APPLICATIONS_MIGRATION_USERNAME") ?: "applications_migration",
                'password'    => getenv("OPG_REFUNDS_DB_APPLICATIONS_MIGRATION_PASSWORD") ?: "applications_migration",
                'dbname'      => getenv("OPG_REFUNDS_DB_APPLICATIONS_NAME") ?: "applications",
            ],
        ],
    ],
    'driver' => [
        'orm_applications' => [
            'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
            'drivers' => [
                'Ingestion\Entity' => 'applications_entities',
            ],
        ],
        'orm_applications_migration' => [
            'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
            'drivers' => [
                'Ingestion\Entity' => 'applications_entities',
            ],
        ],
        'applications_entities' => [
            'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
            'cache' => 'array',
            'paths' => __DIR__ . '/../src/Entity',
        ],
    ],
    'configuration' => [
        'orm_applications' => [
            'proxy_dir' => sys_get_temp_dir(),
        ],
        'orm_applications_migration' => [
            'proxy_dir' => sys_get_temp_dir(),
        ],
        'applications_entities' => [
            'proxy_dir' => sys_get_temp_dir(),
        ],
        'orm_cases' => [
            'proxy_dir' => sys_get_temp_dir(),
        ],
        'orm_cases_migration' => [
            'proxy_dir' => sys_get_temp_dir(),
        ],
        'cases_entities' => [
            'proxy_dir' => sys_get_temp_dir(),
        ],
        'orm_sirius' => [
            'proxy_dir' => sys_get_temp_dir(),
        ],
        'orm_sirius_migration' => [
            'proxy_dir' => sys_get_temp_dir(),
        ],
        'sirius_entities' => [
            'proxy_dir' => sys_get_temp_dir(),
        ],
    ],
];
