<?php
namespace App\Service\Refund\Data;

use Zend\Crypt\PublicKey\Rsa as RsaCipher;


class BankDetailsHandler
{

    private $cipher;
    private $hashSalt;

    public function __construct(RsaCipher $cipher, string $hashSalt)
    {
        $this->cipher = $cipher;
        $this->hashSalt = $hashSalt;
    }

    /**
     * Preps the details ready for storage.
     *
     * @param array $data
     * @return array
     */
    public function process(array $data) : array
    {
        $accountDetails = json_encode([
            'sort-code' => $data['sort-code'],
            'account-number' => $data['account-number'],
        ]);

        //---

        return [
            'name' => $data['name'],
            'hash' => hash('sha512', "{$this->hashSalt}{$accountDetails}"),
            'details' => $this->cipher->encrypt($accountDetails)
        ];
    }

}
