<?php
namespace Dev\Action;

use Applications\Service\DataMigration;
use Interop\Container\ContainerInterface;
use Zend\Crypt\PublicKey\Rsa;

class ViewClaimQueueFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ViewClaimQueueAction(
            $container->get(DataMigration::class),
            $container->get(Rsa::class)
        );
    }
}
