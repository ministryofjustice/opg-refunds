<?php

namespace Auth\Service;

use App\Entity\Cases\Caseworker;
use App\Service\Caseworker as CaseworkerService;
use Auth\Exception\UnauthorizedException;
use Zend\Math\BigInteger\BigInteger;
use Exception;

/**
 * Class AuthenticationService
 * @package Auth\Service
 */
class AuthenticationService
{
    /**
     * Caseworker service
     *
     * @var CaseworkerService
     */
    private $caseworkerService;

    /**
     * The number of seconds before an auth token expires
     *
     * @var int
     */
    private $tokenTtl;

    /**
     * AuthenticationService constructor
     *
     * @param CaseworkerService $caseworkerService
     * @param int $tokenTtl
     */
    public function __construct(CaseworkerService $caseworkerService, int $tokenTtl)
    {
        $this->caseworkerService = $caseworkerService;
        $this->tokenTtl = $tokenTtl;
    }

    /**
     * Validate a caseworker password
     *
     * @param string $email
     * @param string $password
     * @return Caseworker
     * @throws UnauthorizedException|Exception
     */
    public function validatePassword(string $email, string $password)
    {
        /** @var Caseworker $caseworker */
        $caseworker = $this->caseworkerService->findByCredentials($email, $password);

        //  Confirm that the user is active
        if ($caseworker->getStatus() !== 1) {
            throw new UnauthorizedException('User is inactive');
        }

        //  Attempt to generate a token for the user
        do {
            $token = bin2hex(openssl_random_pseudo_bytes(32, $isStrong));

            // Use base62 for shorter tokens
            $token = BigInteger::factory('bcmath')->baseConvert($token, 16, 62);

            if ($isStrong !== true) {
                throw new Exception('Unable to generate a strong token');
            }

            $created = $this->caseworkerService->setToken($caseworker->getId(), $token, time() + $this->tokenTtl);
        } while (!$created);

        return $caseworker;
    }

    /**
     * Validate a request token
     *
     * @param string $token
     * @return Caseworker
     * @throws UnauthorizedException
     */
    public function validateToken(string $token)
    {
        /** @var Caseworker $caseworker */
        $caseworker = $this->caseworkerService->findByToken($token);

        //  Confirm that the user is active
        if ($caseworker->getStatus() !== 1) {
            throw new UnauthorizedException('User is inactive');
        }

        //  Check to see if the token has expired
        if (time() > $caseworker->getTokenExpires()) {
            throw new UnauthorizedException('Token expired');
        }

        //  Increase the token expires value
        $this->caseworkerService->setToken($caseworker->getId(), $caseworker->getToken(),  time() + $this->tokenTtl);

        return $caseworker;
    }
}
