<?php

namespace App\Service\Session;

use Aws\DynamoDb\SessionConnectionInterface as DynamoDbSessionConnectionInterface;
use Zend\Crypt\BlockCipher;

/**
 * Class SessionManager
 * @package App\Service\Session
 */
class SessionManager
{
    /**
     * @var KeyChain Available keys
     */
    private $keys;

    /**
     * @var DynamoDbSessionConnectionInterface
     */
    private $dynamoDbSessionConnection;

    /**
     * @var BlockCipher
     */
    private $blockCipher;

    /**
     * A hash of the data from the last read()
     *
     * @var null|string
     */
    private $lastReadHash = null;

    /**
     * SessionManager constructor
     *
     * @param DynamoDbSessionConnectionInterface $connection
     * @param BlockCipher $blockCipher
     * @param KeyChain $keys
     */
    public function __construct(DynamoDbSessionConnectionInterface $connection, BlockCipher $blockCipher, KeyChain $keys)
    {
        $this->keys = $keys;
        $this->blockCipher = $blockCipher;
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

        /*
         * Determine what key the session was encrypted with.
         * If the key is not found the decrypt will fail and a new session started.
         */
        $data = explode('.', $data['data'], 2);
        $key = $this->keys->offsetGet($data[0]);

        // Decrypt
        $data = $this->blockCipher->setKey($key.$id)->decrypt($data[1]);

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

        // Find the latest key and its ID.
        $key = end($this->keys);
        $keyId = key($this->keys);

        // Encrypt
        $data = $keyId.'.'.$this->blockCipher->setKey($key.$id)->encrypt($data);

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
        return hash('sha512', $id);
    }

    /**
     * Hash used to check if the data has changed since the last read.
     * $id is included to essentially invalidate the hash if a new session ID is created.
     *
     * @param $id
     * @param $data
     * @return string
     */
    private function hashLastRead(string $id, string $data) : string
    {
        return hash('sha512', $id.$data);
    }

}
