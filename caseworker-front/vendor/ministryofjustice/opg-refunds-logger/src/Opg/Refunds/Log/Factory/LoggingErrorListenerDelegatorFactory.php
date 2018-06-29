<?php
namespace Opg\Refunds\Log\Factory;

use Psr\Container\ContainerInterface;
use Zend\Stratigility\Middleware\ErrorHandler;

use Opg\Refunds\Log\ErrorListener;

class LoggingErrorListenerDelegatorFactory
{

    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @return ErrorHandler
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback)
    {
        $errorHandler = $callback();
        $errorHandler->attachListener(
            $container->get(ErrorListener::class)
        );
        return $errorHandler;
    }

}
