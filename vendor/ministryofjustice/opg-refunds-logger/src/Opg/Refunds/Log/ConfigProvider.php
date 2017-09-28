<?php

namespace Opg\Refunds\Log;

use Zend;

class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories'  => [
                    Zend\Log\Logger::class  => Factory\LoggerFactory::class,
                    ErrorListener::class    => Factory\ErrorListenerFactory::class,
                ],
                'delegators' => [
                    Zend\Stratigility\Middleware\ErrorHandler::class => [
                        Factory\LoggingErrorListenerDelegatorFactory::class
                    ],
                ],
            ]
        ];
    }
}
