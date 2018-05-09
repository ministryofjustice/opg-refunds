<?php

namespace App\Service\Session\SaveHandler;

use Aws\DynamoDb\SessionHandler as DynamoDbSessionHandler;
use Zend\Crypt\BlockCipher;
use Zend\Filter\Compress;
use Zend\Filter\Decompress;
use Zend\Session\Exception\RuntimeException;
use Zend\Session\SaveHandler\SaveHandlerInterface;

/**
 * Our saveHandler with encryption AND compression
 *
 * Class EncryptedDynamoDB
 * @package Application\Model\Service\Session\SaveHandler
 */
class EncryptedDynamoDB extends DynamoDbSessionHandler implements SaveHandlerInterface
{
    /**
     * The compression adapter to use (with ZF2 Filters)
     */
    const COMPRESSION_ADAPTER = 'Gz';

    /**
     * Instance of the Block Cipher to use for encryption
     *
     * @var BlockCipher
     */
    private $blockCipher;

    /**
     * Array of currently active keys
     *
     * The format should be:
     *  <int ident> => <string key>
     *
     * The biggest ident value should be treated the the 'current' key
     *
     * @var array
     */
    private $keys;

    /**
     * Sets the pre-configured BlockCipher to use for encryption.
     *
     * @param BlockCipher $blockCipher
     * @param array $keys
     */
    public function setBlockCipher(BlockCipher $blockCipher, array $keys)
    {
        $this->keys = $keys;
        $this->blockCipher = $blockCipher;
    }

    /**
     * Returns the current BlockCipher
     *
     * @return BlockCipher
     */
    private function getBlockCipher()
    {
        if (!$this->blockCipher instanceof BlockCipher) {
            throw new RuntimeException('No session BlockCipher set');
        }

        return $this->blockCipher;
    }

    /**
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        // Return the data from the DynamoDB
        $data = parent::read($id);

        // If there's no data, just return
        if (empty($data)) {
            return '';
        }

        // Split the data into encryption key ident, and actual session data.
        $data = explode('.', $data);

        // If not key ident was found.
        if (count($data) != 2) {
            // For now, assume it's an old style session value, and default to the oldest key.
            // @todo - this should just return null once no old values exist anymore.

            // Ensure keys are sorted, newest to oldest.
            krsort($this->keys);

            // Try the last (oldest) key/value
            $sessionKey = end($this->keys);
            $sessionData = $data[0];
        } else {
            // If the key ident doesn't match a known key...
            if(!isset($this->keys[$data[0]])) {
                return '';
            }

            $sessionKey = $this->keys[$data[0]];
            $sessionData = $data[1];
        }

        // Decrypt and return the data
        $decryptedData =  $this->getBlockCipher()->setKey($sessionKey)->decrypt($sessionData);

        // Decompress the data.
        return (new Decompress(self::COMPRESSION_ADAPTER))->filter($decryptedData);
    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data)
    {
        // Ensure keys are sorted, oldest to newest.
        ksort($this->keys);

        // Use the last (newest) key/value
        $keyValue = end($this->keys);
        $keyIdent = key($this->keys);

        // Compress the data
        $compressedData = (new Compress(self::COMPRESSION_ADAPTER))->filter($data);

        // Encrypt the data
        $encryptedData = $this->getBlockCipher()->setKey($keyValue)->encrypt($compressedData);

        // Add the encryption key ident that was used, separated with a period.
        $encryptedDataWithIdent = $keyIdent . '.' . $encryptedData;

        // Save it to DynamoDB
        return parent::write($id, $encryptedDataWithIdent);
    }
}
