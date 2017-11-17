<?php

namespace App\Service\Reporting;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;

class Reporting implements ApiClientInterface
{
    use ApiClientTrait;

    public function getStats()
    {
        $stats = $this->getApiClient()->httpGet('/v1/stats');

        return $stats;
    }
}