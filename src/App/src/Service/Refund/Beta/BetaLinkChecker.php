<?php
namespace App\Service\Refund\Beta;

use Aws\DynamoDb\DynamoDbClient;

class BetaLinkChecker
{

    private $betaEnabled;
    private $dynamoClient;
    private $dynamoSettings;

    public function __construct(DynamoDbClient $dynamoClient, array $dynamoSettings, bool $betaEnabled)
    {
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

        // Validate signature
        if (false) {
            // @todo ... this
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