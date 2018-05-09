<?php
namespace App\Action\Factory;

use Interop\Container\ContainerInterface;

use App\Action;
use App\Service\Refund\AssistedDigital\LinkToken;

class AssistedDigitalFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['ad']['cookie']['name'])) {
            throw new \UnexpectedValueException('Assisted digital cookie name not configured');
        }

        return new Action\AssistedDigitalAction(
            $container->get(LinkToken::class),
            $config['ad']['cookie']['name']
        );
    }
}
