<?php

namespace App\Service\Authentication;

use Api\Exception\ApiException;
use Api\Service\Client as ApiClient;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Session\SessionManager;

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
     * @var SessionManager
     */
    private $sessionManager;

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
     * @param SessionManager $sessionManager
     */
    public function __construct(ApiClient $client, SessionManager $sessionManager)
    {
        $this->client = $client;
        $this->sessionManager = $sessionManager;
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

            //  Regenerate the session ID post authentication
            $this->sessionManager->regenerateId(true);

            return new Result(Result::SUCCESS, $user);
        } catch (ApiException $apiEx) {
            if ($apiEx->getCode() === 401) {
                return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, [
                    $apiEx->getMessage()
                ]);
            } elseif ($apiEx->getCode() === 403) {
                return new Result(Result::FAILURE_ACCOUNT_LOCKED, null, [
                    $apiEx->getMessage()
                ]);
            }

            return new Result(Result::FAILURE, null, [
                $apiEx->getMessage()
            ]);
        }
    }
}
