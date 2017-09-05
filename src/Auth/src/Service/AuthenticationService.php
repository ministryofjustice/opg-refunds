<?php

namespace Auth\Service;

use Zend\Math\BigInteger\BigInteger;
use Exception;

/**
 * Class AuthenticationService
 * @package Auth\Service
 */
class AuthenticationService
{
    /**
     * TODO - TO BE REMOVED WHEN DB INTEGRATION IS SET UP
     */
    CONST TEMP_VALID_USER_CREDENTIALS = [
        'caseworker_id' => 1,
        'name'          => 'Testy McTest',
        'email'         => 'test@test.com',
        'password'      => '$2y$10$h1.MaEsuzAg6uEqjfJsv9OiIPmlQO1TJKt74cbRveGwZhFLXMHmkq',   //  Hash value of pass1234
        'token'         => 'abcdefghijklmnopqrstuvwxyz',
        'token_expires' => 1536142838,  //  September 5th 2018 - approximately 11:20am
        'status'        => 'active',
        'roles'         => 'caseworker',
    ];

    /**
     * The number of seconds before an auth token expires.
     *
     * @var int
     */
    private $tokenTtl;

    /**
     * AuthenticationService constructor
     *
     * @param int $tokenTtl
     */
    public function __construct(int $tokenTtl)
    {
        //  TODO - Inject DB access here also

        $this->tokenTtl = $tokenTtl;
    }

    /**
     * Validate a token value
     *
     * @param string $token
     * @return bool
     */
    public function validateToken(string $token)
    {
        //  TODO - At this point use the token to get the user from the DB
        $result = ($token == self::TEMP_VALID_USER_CREDENTIALS['token'] ? self::TEMP_VALID_USER_CREDENTIALS : false);

        //  Check that the token has not expired
        if (is_array($result) && isset($result['token_expires'])) {
            if ($result['token_expires'] > time()) {
                //  Set the token again to update the expiry time
                $this->setToken($result, $token);

                return true;
            } else {
                //  The token has expired so clear it
                //  TODO - Update the DB here to remove the token and token expiry...
            }
        }

        return false;
    }

    /**
     * Validate a user password
     *
     * @param $email
     * @param $password
     * @return array|bool
     * @throws Exception
     */
    public function validatePassword(string $email, string $password)
    {
        //  TODO - At this point use the email address to get the user from the DB
        $result = ($email == self::TEMP_VALID_USER_CREDENTIALS['email'] ? self::TEMP_VALID_USER_CREDENTIALS : false);

        //  Verify the password against this user and return the user details
        if (is_array($result)
            && isset($result['status']) && $result['status'] == 'active'
            && isset($result['password']) && password_verify($password, $result['password'])) {

            //  Generate a new token value
            do {
                $token = bin2hex(openssl_random_pseudo_bytes(32, $isStrong));

                // Use base62 for shorter tokens
                $token = BigInteger::factory('bcmath')->baseConvert($token, 16, 62);

                if ($isStrong !== true) {
                    throw new Exception('Unable to generate a strong token');
                }

                //  TODO - For the moment keep the token the same value until we are actually updating it in the DB
                $token = self::TEMP_VALID_USER_CREDENTIALS['token'];

                //  Set the authentication token to be used in subsequent calls to API
                $created = $this->setToken($result, $token);

            } while (!$created);

            //  Return the user details
            return $result;
        }

        return false;
    }

    /**
     *
     * @param array $userData
     * @param string $token
     * @return bool
     */
    private function setToken(array &$userData, string $token)
    {
        $userData['token'] = $token;
        $userData['token_expires'] = time() + $this->tokenTtl;

        //  TODO - Update the DB here to set/re-set the token with the new expiry time

        //  TODO - Ensure that a boolean is returned indicating that the token was set successfully in the DB
        return true;
    }
}
