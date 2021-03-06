<?php

namespace App\Action\Initializers;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use Mezzio\Helper\UrlHelper;

/**
 * Initialize Action middleware with support for the UrlHelper.
 *
 * Class UrlHelperInitializer
 * @package App\Action\Initializers
 */
class UrlHelperInitializer implements InitializerInterface
{

    public function __invoke(ContainerInterface $container, $instance)
    {

        if ($instance instanceof UrlHelperInterface && $container->has(UrlHelper::class)) {
            $instance->setUrlHelper($container->get(UrlHelper::class));
        }
    }
}
