<?php
namespace Dev\Action;

use Applications\Service\DataMigration;
use Interop\Container\ContainerInterface;
use Zend\Crypt\PublicKey\Rsa;

class ViewCaseQueueFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ViewCaseQueueAction(
            $container->get(DataMigration::class),
            $container->get(Rsa::class)
        );
    }
}
