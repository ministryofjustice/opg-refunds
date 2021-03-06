<?php

namespace App\Action\Initializers;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Initialize Action middleware with support for rendering.
 *
 * Class TemplatingSupportInitializer
 * @package App\Action\Initializers
 */
class TemplatingSupportInitializer implements InitializerInterface
{

    public function __invoke(ContainerInterface $container, $instance)
    {

        if ($instance instanceof TemplatingSupportInterface && $container->has(TemplateRendererInterface::class)) {
            $instance->setTemplateRenderer($container->get(TemplateRendererInterface::class));
        }
    }
}
