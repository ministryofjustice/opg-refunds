<?php

namespace App\Service;

use Zend\Math\BigInteger\BigInteger;
use Exception;

/**
 * Class TokenGenerator
 * @package App\Service
 */
class TokenGenerator
{
    /**
     * Generate a token for authentication
     *
     * @return string
     * @throws Exception
     */
    public function generate()
    {
        $token = bin2hex(openssl_random_pseudo_bytes(32, $isStrong));

        // Use base62 for shorter tokens
        $token = BigInteger::factory('bcmath')->baseConvert($token, 16, 62);

        if ($isStrong !== true) {
            throw new Exception('Unable to generate a strong token');
        }

        return $token;
    }
}
