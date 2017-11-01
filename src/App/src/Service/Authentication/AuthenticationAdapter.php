<?php

namespace App\Service\Authentication;

use Api\Exception\ApiException;
use Api\Service\Client as ApiClient;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

/**
 * Class AuthenticationAdapter
 * @package App\Service\Auth
 */
class AuthenticationAdapter implements AdapterInterface
{
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * AuthenticationAdapter constructor
     *
     * @param ApiClient $client
     */
    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Authenticate the credentials via the API
     *
     * @return Result
     */
    public function authenticate()
    {
        try {
            $userData = $this->client->httpPost('/v1/auth', [
                'email'    => $this->email,
                'password' => $this->password,
            ]);

            //  If no exception has been thrown then this is OK - transfer the details to the success result
            $user = new User($userData);

            return new Result(Result::SUCCESS, $user);
        } catch (ApiException $apiEx) {
            return new Result(Result::FAILURE, null, [
                $apiEx->getMessage()
            ]);
       }
    }
}
