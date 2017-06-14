<?php
namespace App\Service\Refund\Data;

use PDO;
use Zend\Crypt\PublicKey\Rsa as RsaCipher;

/**
 * Data Handler for when the DB is directly accessible from the front service.
 *
 * Class DataHandlerLocal
 * @package App\Service\Refund\Data
 */
class DataHandlerLocal implements DataHandlerInterface
{

    private $db;
    private $cipher;
    private $hashSalt;

    public function __construct(PDO $db, RsaCipher $cipher, string $hashSalt)
    {
        $this->db = $db;
        $this->cipher = $cipher;
        $this->hashSalt = $hashSalt;
    }

    public function store(array $data) : string
    {

        $data = $this->prepareData($data);

        //---

        do {
            //----------------------------
            // Generate ID

            $id = random_int(1000000000, 99999999999);

            //----------------------------
            // (Attempt to) save the data

            $sql = 'INSERT INTO refund.application(id, created, version, data) VALUES(:id, :created, :version, :data)';

            $statement = $this->db->prepare($sql);

            try {
                $statement->execute([
                    "id" => $id,
                    "created" => date('r'),
                    "version" => 1,
                    "data" => json_encode($data)
                ]);

                $failed = false;
            } catch (\PDOException $e) {
                // If it's not a duplicate key error, re-throw it.
                if ($e->getCode() != 23505) {
                    throw($e);
                }

                // Else we can just try using a different ID.

                $failed = true;
            }
        } while ($failed);

        //---

        return $id;
    }

    /**
     * Prepare the data, ready to be sent to the DB.
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data) : array
    {

        $accountDetails = $data['account']['details'];

        //------------------------------------------------------
        // Hash sort code and account number

        $detailsToHash = "{$accountDetails['sort-code']}/{$accountDetails['account-number']}";

        $hashedDetails = hash('sha512', $this->hashSalt.$detailsToHash);

        $data['account']['hash'] = $hashedDetails;

        //------------------------------------------------------
        // Replace clear-text details with an encrypted version

        $accountDetails = json_encode($accountDetails);

        $encryptedAccount = $this->cipher->encrypt($accountDetails);

        $data['account']['details'] = $encryptedAccount;

        //---

        return $data;
    }
}
