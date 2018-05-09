<?php
namespace Opg\Refunds\Log\Initializer;

use Zend;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;

class LogSupportInitializer implements InitializerInterface
{
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof LogSupportInterface && $container->has(Zend\Log\Logger::class)) {
            $instance->setLogger($container->get(Zend\Log\Logger::class));
        }
    }
}
