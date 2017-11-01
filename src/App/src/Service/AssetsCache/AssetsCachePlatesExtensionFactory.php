<?php
namespace App\Service\AssetsCache;

use Interop\Container\ContainerInterface;

class AssetsCachePlatesExtensionFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['version']['cache'])) {
            throw new \UnexpectedValueException('Version cache token not configured');
        }

        return new AssetsCachePlatesExtension($config['version']['cache']);
    }

}
