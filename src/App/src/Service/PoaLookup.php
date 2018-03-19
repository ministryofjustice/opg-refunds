<?php

namespace App\Service;

use PDO;
use RuntimeException;

/**
 * Performs a lookup against Meris and Sirius on either case number, or Name & DOB.
 *
 *
 * Class PoaLookup
 * @package App\Service
 */
class PoaLookup {

    private $adapter;

    public function __construct(PDO $adapter)
    {
        $this->adapter = $adapter;
    }


    /**
     * Lookup by case number.
     *
     * @param int $caseNumber
     * @return array
     */
    public function queryByCaseNumber( int $caseNumber ){

        $result = [
            'meris' => $this->runQuery('meris', [
                'case-number' => $caseNumber
            ]),
            'sirius' => $this->runQuery('sirius', [
                'case-number' => $caseNumber
            ])
        ];

        return $result;
    }


    /**
     * Lookup by DOB (required) and optionally first and last name.
     *
     * @param string $dob
     * @param $firstName
     * @param $lastName
     * @return array
     */
    public function queryByDobAndName( string $dob, $firstName, $lastName ){

        $result = [
            'meris' => $this->runQuery('meris', [
                'dob' => $dob,
                'first-name' => $firstName,
                'last-name' => $lastName,
            ]),
            'sirius' => $this->runQuery('sirius', [
                'dob' => $dob,
                'first-name' => $firstName,
                'last-name' => $lastName,
            ])
        ];

        return $result;
    }


    /**
     * Build and execute the query.
     *
     * @param string $table
     * @param array $params
     * @return array
     */
    private function runQuery(string $table, array $params){

        //-------------------------------
        // Create the query (filters)

        $query = '';

        if (!empty($params['case-number'])) {

            $query .= 'case_number = :case';

        } // if

        if (isset($params['dob'])) {
            if (!empty($query)) {
                $query .= " AND ";
            }

            $query .= "data->>'donor-dob' = :dob";
        }

        if (isset($params['first-name'])) {
            if (!empty($query)) {
                $query .= " AND ";
            }

            $query .= "data->>'donor-forename' LIKE :fname";
        }

        if (isset($params['last-name'])) {
            if (!empty($query)) {
                $query .= " AND ";
            }

            $query .= "data->>'donor-lastname' = :lname";
        }

        if (empty($query)) {
            throw new RuntimeException('No filters set');
        }


        //-------------------------------
        // Create the statement

        $sql = "SELECT * FROM {$table} WHERE ";

        $sql .= $query;

        // Sanity check
        $sql .= ' LIMIT 100';

        $statement = $this->adapter->prepare($sql);


        //-------------------------------
        // Bind the parameters

        if (isset($params['dob'])) {
            $statement->bindParam(':dob', $params['dob'], PDO::PARAM_STR);
        }

        if (isset($params['case-number'])) {
            $statement->bindParam(':case', $params['case-number'], PDO::PARAM_INT);
        }

        if (isset($params['first-name'])) {
            $fname = preg_replace('/\s+/', ' ', trim($params['first-name']));
            $fname = mb_strtolower($fname, 'UTF-8');
            $fname = $fname.'%';
            $statement->bindParam(':fname', $fname, PDO::PARAM_STR);
        }

        if (isset($params['last-name'])) {
            $lname = preg_replace('/\s+/', ' ', trim($params['last-name']));
            $lname = mb_strtolower($lname, 'UTF-8');
            $statement->bindParam(':lname', $lname, PDO::PARAM_STR);
        }

        //---

        $statement->execute();

        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        //---

        // JSON decode the data
        $results = array_map(function($v){
            $v['data'] = json_decode($v['data'], true);
            return $v;
        }, $results);

        //---

        // Use the case number (& sequence) as the array's index.
        $results = array_combine(array_map(function($v){
            $index = (string)$v['case_number'];
            if (isset($v['sequence_number'])) {
                $index .= '/' . $v['sequence_number'];
            }
            return $index;
        }, $results), $results);

        //---

        return $results;
    }

    /**
     * Looks for one or more finance record matching a caseId.
     *
     * @param int $caseNumber
     * @return array Finance records
     */
    public function lookupFinanceRecord( int $caseNumber )
    {
        $sql = "SELECT * FROM finance WHERE case_number = :case LIMIT 20";

        $statement = $this->adapter->prepare($sql);

        $statement->bindParam(':case', $caseNumber, PDO::PARAM_INT);

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
