<?php

namespace Api\Service;

use Interop\Container\ContainerInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Zend\Authentication\Storage\Session;
use Zend\Session\Container;

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
        //  TODO - Stop certificate verification temporarily until we have fixed the self signing cert issue - then use $container->get(\Http\Client\HttpClient::class),
        $httpClient = \Http\Adapter\Guzzle6\Client::createWithConfig([
            'verify' => false,
        ]);

        //  Get the end point targets from the config
        $config = $container->get('config');

        //  Get the token value out of the session
        $token = null;
        $session = new Container(Session::NAMESPACE_DEFAULT);

        if (isset($session['storage'])) {
            $user = $session['storage'];
            $token = ($user instanceof User ? $user->getToken() : null);
        }

        return new Client(
            $httpClient,
            $config['api_base_uri'],
            $token
        );
    }
}
