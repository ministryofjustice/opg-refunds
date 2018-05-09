<?php

namespace Api\Service\Initializers;

use Api\Service\Client as ApiClient;
use UnexpectedValueException;

/**
 * Getter and Setter, implementing the ApiClientInterface.
 *
 * Trait ApiClientTrait
 * @package App\Initializers
 */
trait ApiClientTrait
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
    public function getApiClient() : ApiClient
    {
        if (!$this->client instanceof ApiClient) {
            throw new UnexpectedValueException('API client not set');
        }

        return $this->client;
    }
}
