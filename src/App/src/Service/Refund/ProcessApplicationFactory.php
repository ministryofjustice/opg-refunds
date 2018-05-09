<?php
namespace App\Service\Refund;

use Interop\Container\ContainerInterface;

class ProcessApplicationFactory
{

    public function __invoke(ContainerInterface $container)
    {

        $config = $container->get('config');

        if (!isset($config['json']['schema']['path'])) {
            throw new \UnexpectedValueException('JSON schema path not configured');
        }

        if (!is_readable($config['json']['schema']['path'])) {
            throw new \UnexpectedValueException('JSON schema document not readable');
        }

        //---

        return new ProcessApplication(
            $container->get(\Alphagov\Notifications\Client::class),
            $container->get(Data\DataHandlerInterface::class),
            $config['json']['schema']['path']
        );
    }
}
