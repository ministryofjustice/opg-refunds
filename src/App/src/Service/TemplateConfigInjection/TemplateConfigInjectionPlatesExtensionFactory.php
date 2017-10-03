<?php
namespace App\Service\TemplateConfigInjection;

use Interop\Container\ContainerInterface;

/**
 * Creates the TemplateConfigInjectionPlatesExtension.
 *
 * Class TemplateConfigInjectionPlatesExtensionFactory
 * @package App\Service\TemplateConfigInjection
 */
class TemplateConfigInjectionPlatesExtensionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        $config = array_intersect_key(
            $config,
            array_flip([
                // The following top level config keys will be injected into the template.
                'analytics',
            ])
        );

        return new TemplateConfigInjectionPlatesExtension($config);
    }
}