<?php
namespace App\Service\Refund\Data;

use PDO;
use App\Service\Crypt\Hybrid as HybridCipher;

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

    public function __construct(PDO $db, HybridCipher $cipher)
    {
        $this->db = $db;
        $this->cipher = $cipher;
    }

    public function store(array $data) : string
    {

        $data = json_encode($data);

        $data = $this->cipher->encrypt($data);

        //---

        do {
            //----------------------------
            // Generate ID

            $id = random_int(1000000000, 99999999999);

            //----------------------------
            // (Attempt to) save the data

            $sql  = 'INSERT INTO refund.application(id, created, processed, data) ';
            $sql .= 'VALUES(:id, :created, :processed, :data)';

            $statement = $this->db->prepare($sql);

            $statement->bindValue(':id', $id, PDO::PARAM_INT );
            $statement->bindValue(':created', date('r'), PDO::PARAM_STR );
            $statement->bindValue(':processed', false, PDO::PARAM_BOOL );
            $statement->bindValue(':data', $data, PDO::PARAM_LOB );

            try {
                $statement->execute();

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

}
