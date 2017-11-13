<?php
namespace App\Service\AssistedDigital;

use Interop\Container\ContainerInterface;

class LinkTokenFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['ad']['link']['signature']['key'])) {
            throw new \UnexpectedValueException('Assisted digital signature key not configured');
        }

        return new LinkToken($config['ad']['link']['signature']['key']);
    }
}
