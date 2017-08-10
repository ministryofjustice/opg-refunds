<?php
namespace App\Middleware\ProcessingTime;

use Interop\Container\ContainerInterface;

class ProcessingTimeMiddlewareFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['refunds']['processing-time'])) {
            throw new \UnexpectedValueException('Processing time not configured');
        }

        //---

        $time = $config['refunds']['processing-time'];

        // Sense check the value.
        if (strtotime($time) == false) {
            throw new \UnexpectedValueException('Invalid time not configured');
        }

        //---

        return new ProcessingTimeMiddleware($time);
    }

}