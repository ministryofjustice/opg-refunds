<?php

namespace App\Service;

use Alphagov\Notifications\Client as NotifyClient;
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
            $container->get(NotifyClient::class)
        );
    }
}