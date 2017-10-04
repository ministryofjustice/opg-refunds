<?php
namespace Dev\Action;

use Ingestion\Service\ApplicationIngestion;
use Interop\Container\ContainerInterface;
use Zend\Crypt\PublicKey\Rsa;

class ViewClaimQueueFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ViewClaimQueueAction(
            $container->get(ApplicationIngestion::class),
            $container->get(Rsa::class)
        );
    }
}
