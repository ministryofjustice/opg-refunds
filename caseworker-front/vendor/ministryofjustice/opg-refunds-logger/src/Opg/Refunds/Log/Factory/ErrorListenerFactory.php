<?php
namespace Opg\Refunds\Log\Factory;

use Zend\Log\Logger;
use Opg\Refunds\Log\ErrorListener;

use Interop\Container\ContainerInterface;

class ErrorListenerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['log']['priorities']['500'])) {
            throw new \UnexpectedValueException('Log priority for 500 not configured');
        }

        return new ErrorListener(
            $container->get(Logger::class),
            $config['log']['priorities']['500']
        );
    }
}
