<?php
namespace App\Crypt;

use Zend\Crypt\Hybrid as ZendHybrid;

/**
 * Extends Zend's Hybrid class to allowed RSA to use pre-set keys.
 *
 * Class Hybrid
 * @package App\Crypt
 */
class Hybrid extends ZendHybrid
{
    public function encrypt($plaintext, $keys = null)
    {
        // If key is null, try and pull it out of the RSA instance.
        if (is_null($keys)) {
            $keys = $this->getRsaInstance()->getOptions()->getPublicKey();
        }

        return parent::encrypt($plaintext, $keys);
    }

    public function decrypt($msg, $privateKey = null, $passPhrase = null, $id = null)
    {
        if (is_null($privateKey)) {
            $privateKey = $this->getRsaInstance()->getOptions()->getPrivateKey();
        }

        return parent::decrypt($msg, $privateKey, $passPhrase, $id);
    }
}
