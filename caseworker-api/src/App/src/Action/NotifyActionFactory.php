<?php

namespace App\Action;

use App\Service\Notify as NotifyService;
use Interop\Container\ContainerInterface;

/**
 * Class NotifyActionFactory
 * @package App\Action
 */
class NotifyActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return NotifyAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new NotifyAction(
            $container->get(NotifyService::class),
            $config = $container->get('config')['notify']
        );
    }
}
