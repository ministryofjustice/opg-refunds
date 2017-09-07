<?php

namespace App\Action;

use App\Service\Caseworker as CaseworkerService;
use Interop\Container\ContainerInterface;

/**
 * Class CaseworkerActionFactory
 * @package App\Action
 */
class CaseworkerActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return CaseworkerAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new CaseworkerAction(
            $container->get(CaseworkerService::class)
        );
    }
}
