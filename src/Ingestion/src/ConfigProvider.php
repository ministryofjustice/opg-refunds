<?php

namespace Ingestion;

/**
 * The configuration provider for the Ingestion module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'doctrine'     => include __DIR__ . '/../config/doctrine.php',
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'invokables' => [
            ],
            'factories'  => [
                'doctrine.entity_manager.orm_applications_migration' => [\ContainerInteropDoctrine\EntityManagerFactory::class, 'orm_applications_migration'],
                'doctrine.entity_manager.orm_applications' => [\ContainerInteropDoctrine\EntityManagerFactory::class, 'orm_applications'],

                //  Services
                Service\DataMigration::class => Service\DataMigrationFactory::class
            ],
        ];
    }
}
