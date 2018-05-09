<?php
namespace Opg\Refunds\Log\Initializer;

use UnexpectedValueException;

use Zend\Log\Logger;

trait LogSupportTrait
{
    private $logger;

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger() : Logger
    {
        if (!( $this->logger instanceof Logger )) {
            throw new UnexpectedValueException('Logger not set');
        }

        return $this->logger;
    }
}
