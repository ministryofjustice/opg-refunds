<?php

namespace App\Action;

use Api\Service\Client as ApiClient;
use UnexpectedValueException;

/**
 * Class AbstractApiClientAction
 * @package App\Action
 */
abstract class AbstractApiClientAction extends AbstractAction
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
