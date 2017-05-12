<?php

namespace App\Action\Templating;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Initializer Action middleware with support for rendering.
 *
 * Class TemplatingSupportInitializer
 * @package App\Action\Templating
 */
class TemplatingSupportInitializer implements InitializerInterface
{

    public function __invoke(ContainerInterface $container, $instance)
    {

        if( $instance instanceof TemplatingSupportInterface && $container->has(TemplateRendererInterface::class) ){

            $instance->setTemplateRenderer( $container->get(TemplateRendererInterface::class) );

        }

    }

}
