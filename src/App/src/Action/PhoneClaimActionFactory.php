<?php

namespace App\Action;

use App\Service\AssistedDigital\LinkToken as LinkTokenGenerator;
use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;

class PhoneClaimActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return PhoneClaimAction
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['ad']['link']['domain'])) {
            throw new \UnexpectedValueException('Assisted digital link domain not configured');
        }

        return new PhoneClaimAction(
            $container->get(LinkTokenGenerator::class),
            $container->get(UserService::class),
            $config['ad']['link']['domain']
        );
    }
}