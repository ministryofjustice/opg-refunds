<?php
namespace Opg\Refunds\Log\Initializer;

use Zend\Log\Logger;

interface LogSupportInterface
{
    public function setLogger(Logger $logger);

    public function getLogger() : Logger;
}
