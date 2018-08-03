<?php

namespace Auth\Action\Initializers;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;

/**
 * Initialize Action middleware with the API client
 *
 * Class UserInitializer
 * @package Auth\Action\Initializers
 */
class UserInitializer implements InitializerInterface
{

    /**
     * Initialize the given instance
     *
     * @param  ContainerInterface $container
     * @param  object $instance
     * @return void
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        /*if ($instance instanceof AbstractUserAction && $container->has(ApiClient::class)
        ) {
            $instance->setApiClient($container->get(ApiClient::class));
        }*/
    }
}
