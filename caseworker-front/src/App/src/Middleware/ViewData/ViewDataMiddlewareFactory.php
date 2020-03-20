<?php

namespace App\Middleware\ViewData;

use Interop\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Class ViewDataMiddlewareFactory
 * @package App\Middleware\ViewData
 */
class ViewDataMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return ViewDataMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ViewDataMiddleware(
            $container->get(TemplateRendererInterface::class)
        );
    }
}
