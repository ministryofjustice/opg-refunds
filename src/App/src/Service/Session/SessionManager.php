<?php
namespace App\Service\Session;

use Aws\DynamoDb\SessionConnectionInterface as DynamoDbSessionConnectionInterface;

use Zend\Crypt\BlockCipher;

class SessionManager
{

    /**
     * @var DynamoDbSessionConnectionInterface
     */
    private $dynamoDbSessionConnection;

    /**
     * @var BlockCipher
     */
    private $blockCipher;

    /**
     * A hash of the data as it was last read.
     *
     * @var null|string
     */
    private $lastReadHash = null;

    public function __construct(DynamoDbSessionConnectionInterface $connection, BlockCipher $blockCipher)
    {
        $this->blockCipher = $blockCipher;
        $this->dynamoDbSessionConnection = $connection;
    }

    /**
     * Returns session data for the passed ID.
     *
     * @param string $id
     * @return array
     */
    public function read(string $id) : array
    {

        // Read
        $data = $this->dynamoDbSessionConnection->read($this->hashId($id));

        // Check we have the expected fields.
        if (!isset($data['data']) || !isset($data['expires'])) {
            return [];
        }

        // Check it has not expired
        if ($data['expires'] < time()) {
            $this->delete($id);
            return [];
        }

        $data = base64_decode($data['data']);

        // Decrypt
        $data = $this->getBlockCipher($id)->decrypt($data);

        // Decompress
        $data = gzinflate($data);

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

        // Encrypt
        $data = $this->getBlockCipher($id)->encrypt($data);

        // Save
        $this->dynamoDbSessionConnection->write($this->hashId($id), base64_encode($data), $changed);
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

    /**
     * Returns a clone of the Block Cipher, with the correct key set.
     *
     * @param string $id
     * @return BlockCipher
     */
    private function getBlockCipher(string $id) : BlockCipher
    {
        $cipher = clone $this->blockCipher;
        $cipher->setKey($cipher->getKey().$id);
        return $cipher;
    }
}
