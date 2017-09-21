<?php

namespace App\Service\Auth;

use Api\Exception\ApiException;
use Api\Service\Client as ApiClient;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

/**
 * Class AuthAdapter
 * @package App\Service\Auth
 */
class AuthAdapter implements AdapterInterface
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
     * AuthAdapter constructor
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
            $userData = $this->client->authenticate($this->email, $this->password);

            //  If no exception has been thrown then this is OK - transfer the details to the success result
            $user = new User($userData);

            return new Result(Result::SUCCESS, $user);
        } catch (ApiException $apiEx) {
            $response = $apiEx->getResponse();

            return new Result(Result::FAILURE, null, [
                $response->getReasonPhrase()
            ]);
       }
    }
}
