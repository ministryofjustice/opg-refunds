<?php

namespace App\Service;

use App\Service\Account as AccountService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimFactory
 * @package App\Service
 */
class ClaimFactory
{
    /**
     * @param ContainerInterface $container
     * @return Claim
     */
    public function __invoke(ContainerInterface $container)
    {
        return new Claim(
            $container->get('doctrine.entity_manager.orm_cases'),
            $container->get(PoaLookup::class),
            $container->get(AccountService::class)
        );
    }
}
