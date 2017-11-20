<?php
namespace App\Service\AssistedDigital;

use UnexpectedValueException;
use Base64Url\Base64Url;
use Zend\Crypt\Key\Derivation\Pbkdf2;

class LinkToken
{
    /**
     * 256-bit hex key
     *
     * @var string
     */
    private $key;


    public function __construct(string $key)
    {
        if (mb_strlen( hex2bin($key), '8bit' ) != 32) {
            throw new UnexpectedValueException('Invalid key; 256-bit hex value expected');
        }

        $this->key = $key;
    }


    /**
     * Generate a signed token containing the passed payload.
     *
     * @param array $payload
     * @return string
     */
    public function generate(array $payload) : string
    {
        if (empty($payload)) {
            throw new UnexpectedValueException('$payload cannot be empty');
        }

        //---

        // Added metadata to payload.
        $payload = [
            'app' => $payload,
            'meta' => [
                'day' => (int)floor(time() / 86400),
                'salt' => bin2hex(random_bytes(4)),
            ],
        ];

        //---

        $iv = random_bytes(32);

        $hash = Pbkdf2::calc(
            'sha256',
            $this->key,
            $iv,
            5000,
            256 * 2
        );

        $payload = gzdeflate(json_encode($payload));

        $signature = hash_hmac('sha256', $payload, $hash, true);

        $token = $signature.$iv.$payload;

        return Base64Url::encode($token);
    }


    /**
     * Extracts the payload from the passed token and verifies it has:
     *  - A valid HMAC signature; and
     *  - Is valid for 'today'.
     *
     * On failure a UnexpectedValueException is thrown.
     *
     * @param string $token
     * @return array - The original payload passed.
     */
    public function verify(string $token) : array
    {
        // Extract the token's components

        $token = Base64Url::decode($token);

        // The first 32 bytes is the hmac
        $hmac = mb_substr($token, 0, 32, '8bit');

        // The second 32 bytes is the iv
        $iv = mb_substr($token, 32, 32, '8bit');

        // Everything else is the compressed payload
        $payload = mb_substr($token, 64, null, '8bit');

        //---

        // Verify the HMAC

        $hash = Pbkdf2::calc(
            'sha256',
            $this->key,
            $iv,
            5000,
            256 * 2
        );

        $generatedHmac = hash_hmac('sha256', $payload, $hash, true);

        if (!hash_equals($generatedHmac, $hmac)) {
            throw new UnexpectedValueException('The token has an invalid signature');
        }

        //---

        // Decompress the payload

        $payload = gzinflate($payload);

        if ($payload === false) {
            throw new UnexpectedValueException('Payload cannot be decompressed');
        }

        //---

        // Decode the JSON

        $json = json_decode($payload, true);

        if (!is_array($json)) {
            throw new UnexpectedValueException('Payload is invalid JSON');
        }

        //---

        // Verify token is valid for 'today'

        if (!isset($json['meta']['day']) || $json['meta']['day'] != floor(time() / 86400)) {
            throw new UnexpectedValueException('Link has expired');
        }

        //---

        return $json['app'];
    }
}
