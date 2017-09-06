<?php
namespace App\Service\Refund;

use Interop\Container\ContainerInterface;

class ProcessApplicationFactory
{

    public function __invoke(ContainerInterface $container)
    {

        return new ProcessApplication(
            $container->get(\Alphagov\Notifications\Client::class),
            $container->get(Data\DataHandlerInterface::class)
        );
    }
}
