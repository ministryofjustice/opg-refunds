<?php

namespace App\Action\Initializers;

use Api\Service\Client as ApiClient;
use App\Action\AbstractApiClientAction;
use App\Service\AbstractApiClientService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;

/**
 * Initialize Action middleware with the API client
 *
 * Class ApiClientInitializer
 * @package App\Action\Initializers
 */
class ApiClientInitializer implements InitializerInterface
{
    /**
     * @param ContainerInterface $container
     * @param object $instance
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if (($instance instanceof AbstractApiClientAction || $instance instanceof AbstractApiClientService)
            && $container->has(ApiClient::class)
        ) {
            $instance->setApiClient($container->get(ApiClient::class));
        }
    }
}
