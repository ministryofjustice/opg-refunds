<?php
namespace App\Action\Factory;

use Interop\Container\ContainerInterface;

use App\Action;

class HealthCheckFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Action\HealthCheckAction($container);
    }
}
