<?php

namespace App\Service;

use Api\Service\Client as ApiClient;
use UnexpectedValueException;

/**
 * Class AbstractApiClientService
 * @package App\Action
 */
abstract class AbstractApiClientService
{
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @param ApiClient $client
     */
    public function setApiClient(ApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return ApiClient
     */
    protected function getApiClient() : ApiClient
    {
        if (!$this->client instanceof ApiClient) {
            throw new UnexpectedValueException('API client not set');
        }

        return $this->client;
    }
}
