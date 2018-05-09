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
        $token = bin2hex(random_bytes(32));

        // Use base62 for shorter tokens
        $token = BigInteger::factory('bcmath')->baseConvert($token, 16, 62);

        return $token;
    }
}
