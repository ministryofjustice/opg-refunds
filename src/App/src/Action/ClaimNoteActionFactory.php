<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimNoteActionFactory
 * @package App\Action
 */
class ClaimNoteActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimNoteAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimNoteAction(
            $container->get(ClaimService::class)
        );
    }
}