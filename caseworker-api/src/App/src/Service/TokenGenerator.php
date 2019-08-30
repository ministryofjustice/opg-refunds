<?php

namespace App\Service;

use ParagonIE\ConstantTime;
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
        return ConstantTime\Base64UrlSafe::encode(random_bytes(32));
    }
}
