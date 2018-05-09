<?php
namespace App\Action\Factory;

use Interop\Container\ContainerInterface;
use App\Action;

class HomePageFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['home']['redirect'])) {
            throw new \UnexpectedValueException('Home page redirect not configured');
        }

        return new Action\HomePageAction(
            $config['home']['redirect']
        );
    }
}
