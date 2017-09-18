<?php

namespace App\Service;

use Interop\Container\ContainerInterface;
use Zend\Crypt\PublicKey\Rsa;

/**
 * Class RefundCaseFactory
 * @package App\Service
 */
class RefundCaseFactory
{
    /**
     * @param ContainerInterface $container
     * @return RefundCase
     */
    public function __invoke(ContainerInterface $container)
    {
        return new RefundCase(
            $container->get('doctrine.entity_manager.orm_cases'),
            $container->get(Rsa::class)
        );
    }
}
