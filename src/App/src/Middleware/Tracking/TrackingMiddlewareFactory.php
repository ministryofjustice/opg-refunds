<?php
namespace App\Middleware\Tracking;

use Interop\Container\ContainerInterface;
use League\Plates\Engine as PlatesEngine;

class TrackingMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new TrackingMiddleware(
            $container->get(PlatesEngine::class)
        );
    }
}
