<?php
namespace App\Action;

use Interop\Container\ContainerInterface;

class HealthCheckFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new HealthCheckAction($container);
    }
}
