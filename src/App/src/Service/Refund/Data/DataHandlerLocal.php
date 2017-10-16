<?php
namespace App\Service\Refund\Data;

use PDO;

use Opg\Refunds\Log\Initializer;

/**
 * Data Handler for when the DB is directly accessible from the front service.
 *
 * Class DataHandlerLocal
 * @package App\Service\Refund\Data
 */
class DataHandlerLocal implements DataHandlerInterface, Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;

    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function store(array $data) : string
    {

        $data = json_encode($data);

        $data = gzdeflate($data);

        //---

        do {
            //----------------------------
            // Generate ID

            $id = random_int(1000000000, 99999999999);

            //----------------------------
            // (Attempt to) save the data

            $sql  = 'INSERT INTO application(id, created, processed, data) ';
            $sql .= 'VALUES(:id, :created, :processed, :data)';

            $statement = $this->db->prepare($sql);

            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->bindValue(':created', date('r'), PDO::PARAM_STR);
            $statement->bindValue(':processed', false, PDO::PARAM_BOOL);
            $statement->bindValue(':data', $data, PDO::PARAM_LOB);

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
                $this->getLogger()->notice('ID clash on database insert', ['id'=>$id]);
            }
        } while ($failed);

        //---

        return $id;
    }
}
