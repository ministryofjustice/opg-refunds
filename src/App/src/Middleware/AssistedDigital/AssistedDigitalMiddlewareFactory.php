<?php
namespace App\Middleware\AssistedDigital;

use Interop\Container\ContainerInterface;
use App\Service\Refund\AssistedDigital\LinkToken;
use League\Plates\Engine as PlatesEngine;

class AssistedDigitalMiddlewareFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['ad']['cookie']['name'])) {
            throw new \UnexpectedValueException('Assisted digital cookie name not configured');
        }

        return new AssistedDigitalMiddleware(
            $container->get(LinkToken::class),
            $config['ad']['cookie']['name'],
            $container->get(PlatesEngine::class)
        );
    }

}
