<?php

namespace Api\Service;

use Http\Client\HttpClient;
use Interop\Container\ContainerInterface;

/**
 * Class ClientFactory
 * @package Api\Service
 */
class ClientFactory
{
    /**
     * @param ContainerInterface $container
     * @return Client
     */
    public function __invoke(ContainerInterface $container)
    {
        //  TODO - Stop certificate verification temporarily until we have fixed the self signing cert issue - then use $container->get(HttpClient::class),
        $httpClient = \Http\Adapter\Guzzle6\Client::createWithConfig([
            'verify' => false,
        ]);

        //  Get the end point targets from the config
        $config = $container->get('config');

        //  TODO - Fix this - how to get the token out of the session the "proper" way
        $token = null;

        if (isset($_SESSION['Zend_Auth']['storage']['token'])) {
            $token = $_SESSION['Zend_Auth']['storage']['token'];
        }

        return new Client(
            $httpClient,
            $config['api_base_uri'],
            $token
        );
    }
}
