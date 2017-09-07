<?php

namespace App\Action;

use App\Service\Caseworker as CaseworkerService;
use Interop\Container\ContainerInterface;

/**
 * Class UserActionFactory
 * @package App\Action
 */
class UserActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return UserAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new UserAction(
            $container->get(CaseworkerService::class)
        );
    }
}
