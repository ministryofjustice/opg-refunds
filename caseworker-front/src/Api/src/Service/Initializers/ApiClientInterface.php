<?php

namespace Api\Service\Initializers;

use Api\Service\Client as ApiClient;

/**
 * Interface ApiClientInterface
 * @package Api\Action\Initializers
 */
interface ApiClientInterface
{
    /**
     * @param ApiClient $client
     */
    public function setApiClient(ApiClient $client);

    /**
     * @return ApiClient
     */
    public function getApiClient() : ApiClient;
}
