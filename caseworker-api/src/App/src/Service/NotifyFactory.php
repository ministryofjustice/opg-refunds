<?php

namespace App\Service;

use Alphagov\Notifications\Client as NotifyClient;
use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class NotifyFactory
 * @package App\Service
 */
class NotifyFactory
{
    /**
     * @param ContainerInterface $container
     * @return Notify
     */
    public function __invoke(ContainerInterface $container)
    {
        return new Notify(
            $container->get('doctrine.entity_manager.orm_cases'),
            $container->get(NotifyClient::class),
            $container->get(ClaimService::class)
        );
    }
}