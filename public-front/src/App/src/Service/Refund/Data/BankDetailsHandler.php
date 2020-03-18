<?php
namespace App\Service\Refund\Data;

use Aws\Kms\KmsClient;
use Laminas\Crypt\PublicKey\Rsa as RsaCipher;

class BankDetailsHandler
{

    private $kmsKeyId;
    private $kmsClient;
    private $hashSalt;

    public function __construct(KmsClient $kmsClient, string $kmsKeyId, string $hashSalt)
    {
        $this->kmsKeyId = $kmsKeyId;
        $this->kmsClient = $kmsClient;
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

        $kmsResult = $this->kmsClient->encrypt([
            'KeyId' => $this->kmsKeyId,
            'Plaintext' => $accountDetails
        ]);

        $cipherAccountDetails = base64_encode($kmsResult->get('CiphertextBlob'));

        //---

        return [
            'name' => $data['name'],
            'hash' => hash('sha512', $this->hashSalt.$accountDetails),
            'details' => $cipherAccountDetails
        ];
    }
}
