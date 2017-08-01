<?php
namespace App\Service\Crypt;

use Zend\Crypt\BlockCipher;
use Zend\Crypt\PublicKey\Rsa;

/**
 * Similar to Zend\Crypt\Hybrid, but supports (only) a RSA cipher being passed with a public key already set.
 *
 * Class Hybrid
 * @package App\Service\Crypt
 */
class Hybrid
{

    private $rsaCipher;

    public function __construct(Rsa $rsaCipher)
    {
        $this->rsaCipher = $rsaCipher;
    }

    public function encrypt($plaintext)
    {

        $blockCipher = BlockCipher::factory('openssl', ['algo' => 'aes']);

        $key = random_bytes($blockCipher->getCipher()->getKeySize());

        $blockCipher->setKey($key);

        $cipherText = $blockCipher->encrypt($plaintext);

        //---

        $encryptedKey = $this->rsaCipher->encrypt($key);

        if ($this->rsaCipher->getOptions()->getBinaryOutput()) {
            $encryptedKey = base64_encode($encryptedKey);
        }

        return $encryptedKey.';'.$cipherText;
    }

}