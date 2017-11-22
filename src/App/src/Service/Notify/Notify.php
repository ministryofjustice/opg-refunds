<?php

namespace App\Service\Notify;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;

/**
 * Class Notify
 * @package App\Service\Notify
 */
class Notify implements ApiClientInterface
{
    use ApiClientTrait;

    public function notifyAll()
    {
        $notified = $this->getApiClient()->httpPost("/v1/notify", []);

        return $notified;
    }
}