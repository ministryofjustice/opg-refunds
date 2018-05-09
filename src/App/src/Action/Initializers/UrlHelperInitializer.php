<?php

namespace App\Action\Initializers;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Helper\UrlHelper;
use Zend\ServiceManager\Initializer\InitializerInterface;

/**
 * Initialize Action middleware with support for the UrlHelper.
 *
 * Class UrlHelperInitializer
 * @package App\Action\Initializers
 */
class UrlHelperInitializer implements InitializerInterface
{
    /**
     * @param ContainerInterface $container
     * @param object $instance
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof UrlHelperInterface && $container->has(UrlHelper::class)) {
            $instance->setUrlHelper($container->get(UrlHelper::class));
        }
    }
}
