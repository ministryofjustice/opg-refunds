<?php
namespace App\Service\Refund\Beta;

use Zend\Crypt\Key\Derivation\Pbkdf2;
use Zend\Math\BigInteger\BigInteger;

use Aws\DynamoDb\DynamoDbClient;

class BetaLinkChecker
{

    private $key;
    private $betaEnabled;
    private $dynamoClient;
    private $dynamoSettings;

    public function __construct(
        DynamoDbClient $dynamoClient,
        array $dynamoSettings,
        string $key,
        bool $betaEnabled
    ){
        $this->key = $key;
        $this->dynamoClient = $dynamoClient;
        $this->dynamoSettings = $dynamoSettings;
        $this->betaEnabled = $betaEnabled;
    }

    /**
     * Checks if the link:
     * - Contains teh expected details
     * - Has not expired
     * - Has a valid signature
     *
     * @param $id
     * @param $expires
     * @param $signature
     * @return bool
     */
    public function isLinkValid($id, $expires, $signature)
    {
        if ($this->betaEnabled !== true) {
            return true;
        }

        //---

        if (empty($id) || empty($expires) || empty($signature)) {
            return 'missing-data';
        }

        if (!is_numeric($expires) || time() > $expires) {
            return 'expired';
        }

        //---

        // Validate signature

        $signature = BigInteger::factory('bcmath')->baseConvert( $signature, 62, 16 );
        $iv = hex2bin(mb_substr($signature, 0, 64, '8bit'));
        $hmac = mb_substr($signature, 64, null, '8bit');

        $hash = Pbkdf2::calc(
            'sha256',
            $this->key,
            $iv,
            5000,
            256 * 2
        );

        $details = "{$id}/{$expires}";
        $generatedHmac = hash_hmac('sha256', $details, $hash);

        if (!hash_equals($generatedHmac, $hmac)) {
            return 'invalid-signature';
        }

        return true;
    }

    /**
     * Checks against the DB if a given link has been used.
     *
     * @param $id
     * @return bool
     */
    public function hasLinkBeenUsed($id)
    {
        if ($this->betaEnabled !== true) {
            return false;
        }

        //---

        $result = $this->dynamoClient->getItem([
            'TableName'      => $this->dynamoSettings['table_name'],
            'Key'            => [ 'id'=> ['N' => $id] ],
            'ConsistentRead' => true,
        ]);

        // If an 'Item' is returned, the link has been used.
        return isset($result['Item']);
    }

    /**
     * Flags a link as having been used in the DB.
     *
     * @param $id
     * @param $applicationReference
     */
    public function flagLinkAsUsed($id, $applicationReference)
    {
        if ($this->betaEnabled !== true) {
            return;
        }

        //---

        if (!is_numeric($id)) {
            throw new \UnexpectedValueException('Passed beta ID is invalid.');
        }

        $this->dynamoClient->putItem([
            'TableName'      => $this->dynamoSettings['table_name'],
            'Item'           => [
                'id'        => ['N' => $id],
                'date'      => ['S' => gmdate(\DateTime::ISO8601)],
                'reference' => ['N' => $applicationReference],
            ],
        ]);
    }
}