<?php
namespace App\Action\Factory;

use Interop\Container\ContainerInterface;

use App\Action;
use App\Service\Refund\Beta\BetaLinkChecker;

class BetaFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['beta']['cookie']['name'])) {
            throw new \UnexpectedValueException('Beta cookie name not configured');
        }

        if (!isset($config['beta']['enabled'])) {
            throw new \UnexpectedValueException('Beta enabled not configured');
        }

        return new Action\BetaAction(
            $container->get(BetaLinkChecker::class),
            $config['beta']['cookie']['name'],
            $config['beta']['enabled']
        );
    }
}
