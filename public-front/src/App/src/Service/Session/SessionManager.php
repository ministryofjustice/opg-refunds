<?php
namespace App\Service\Session;

use Aws\DynamoDb\SessionConnectionInterface as DynamoDbSessionConnectionInterface;

class SessionManager
{
    /**
     * @var DynamoDbSessionConnectionInterface
     */
    private $dynamoDbSessionConnection;

    /**
     * A hash of the data from the last read()
     *
     * @var null|string
     */
    private $lastReadHash = null;


    public function __construct(DynamoDbSessionConnectionInterface $connection)
    {
        $this->dynamoDbSessionConnection = $connection;
    }

    /**
     * Returns session data for the passed ID.
     *
     * @param string $id
     * @return array|false
     */
    public function read(string $id)
    {
        // Read
        $data = $this->dynamoDbSessionConnection->read($this->hashId($id));

        // Check we have the expected fields.
        if (!isset($data['data']) || !isset($data['expires'])) {
            return false;
        }

        // Check it has not expired
        if ($data['expires'] < time()) {
            $this->delete($id);
            return false;
        }

        // Decode
        $data = base64_decode($data['data']);

        // Decompress
        $data = gzinflate($data);

        // Record a hash of the data
        $this->lastReadHash = $this->hashLastRead($id, $data);

        // Decode
        $data = json_decode($data, true);

        return $data;
    }

    /**
     * Writes the passed data to the session ID.
     *
     * @param string $id
     * @param array $data
     */
    public function write(string $id, array $data)
    {
        // Sort the data to aid with checking if it has changed.
        ksort($data);

        // Encode
        $data = json_encode($data);

        // Flag whether the data has changed since the last read.
        $changed = $this->lastReadHash !== $this->hashLastRead($id, $data);

        // Compress
        $data = gzdeflate($data);

        // Encode
        $data = base64_encode($data);

        // Save
        $this->dynamoDbSessionConnection->write($this->hashId($id), $data, $changed);
    }

    /**
     * Deletes the passed session ID
     *
     * @param string $id
     */
    public function delete(string $id)
    {
        $this->dynamoDbSessionConnection->delete($this->hashId($id));
    }

    /**
     * Return a hashed version of the session ID to use as the session's key in the DB.
     *
     * @param string $id
     * @return string
     */
    private function hashId(string $id) : string
    {
        return hash('sha256', $id);
    }

    /**
     * Hash used to check if the data has changed since the last read.
     * $id is included to invalidate the hash if a new session ID is created.
     *
     * @param $id
     * @param $data
     * @return string
     */
    private function hashLastRead(string $id, string $data) : string
    {
        return hash('sha256', $id.$data);
    }
}
