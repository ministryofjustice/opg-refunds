<?php

namespace Api\Service\Initializers;

use Api\Service\Client as ApiClient;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;

/**
 * Initialize trait with the API client
 *
 * Class ApiClientInitializer
 * @package App\Initializers
 */
class ApiClientInitializer implements InitializerInterface
{
    /**
     * @param ContainerInterface $container
     * @param object $instance
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof ApiClientInterface && $container->has(ApiClient::class)) {
            $instance->setApiClient($container->get(ApiClient::class));
        }
    }
}
