<?php
namespace AppTest\Service\Session;

use PHPUnit\Framework\TestCase;

use App\Service\Session\SessionManager;

use Zend\Crypt\BlockCipher;
use Aws\DynamoDb\SessionConnectionInterface as DynamoDbSessionConnectionInterface;

class SessionManagerTest extends TestCase
{

    const TEST_SESSION_ID = 'test-session-id';

    const BASE_ENC_KEY = 'test-encryption-key';

    const HASH_ALGORITHM = 'sha512';

    private $dynamoDb;
    private $blockCipher;

    protected function setUp()
    {

        $this->dynamoDb = $this->prophesize(DynamoDbSessionConnectionInterface::class);

        $this->blockCipher = $this->prophesize(BlockCipher::class);
        $this->blockCipher->getKey()->willReturn( self::BASE_ENC_KEY );

    }

    public function testCanInstantiate()
    {

        $sm = new SessionManager( $this->dynamoDb->reveal(), $this->blockCipher->reveal() );
        $this->assertInstanceOf(SessionManager::class, $sm);

    }

    /**
     * This assumed the hash is sha512.
     * As this is hardcoded in the concrete implementation, this seems fair.
     */
    public function testCanDelete()
    {

        // We expect delete() on the DynamoDD Client to be called.
        $this->dynamoDb->delete( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->shouldBeCalled();

        $sm = new SessionManager( $this->dynamoDb->reveal(), $this->blockCipher->reveal() );
        $this->assertInstanceOf(SessionManager::class, $sm);

        $sm->delete( self::TEST_SESSION_ID );

    }

    public function testCanWriteWithNewData()
    {

        $data = [ 'test'=>true ];
        $testEncryptedData = '-data-';

        $sm = new SessionManager( $this->dynamoDb->reveal(), $this->blockCipher->reveal() );
        $this->assertInstanceOf(SessionManager::class, $sm);

        // We expect the key to be re-set to include the ID.
        $this->blockCipher->setKey( self::BASE_ENC_KEY.self::TEST_SESSION_ID )->shouldBeCalled();

        // We expect the compressed data to be passed to the Cipher.
        $this->blockCipher->encrypt( gzencode(json_encode( $data )) )->shouldBeCalled();

        // Setup some test encrypted data.
        $this->blockCipher->encrypt( gzencode(json_encode( $data )) )->willReturn( $testEncryptedData );

        // We expect a base64 version of that data to be saved to DynamoDD
        $this->dynamoDb->write( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ), base64_encode($testEncryptedData), true )->shouldBeCalled();

        $sm->write( self::TEST_SESSION_ID, $data );

    }


    public function testReadDataWhenExpired()
    {

        $sm = new SessionManager( $this->dynamoDb->reveal(), $this->blockCipher->reveal() );
        $this->assertInstanceOf(SessionManager::class, $sm);

        $this->dynamoDb->read( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->shouldBeCalled();
        $this->dynamoDb->delete( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->shouldBeCalled();

        $this->dynamoDb->read( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->willReturn([
            'expires' => 123
        ]);

        $result = $sm->read( self::TEST_SESSION_ID );

        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);


    }


    public function testReadDataWhenEmpty()
    {

        $sm = new SessionManager( $this->dynamoDb->reveal(), $this->blockCipher->reveal() );
        $this->assertInstanceOf(SessionManager::class, $sm);

        // As the data is just empty, not expired, we should not see a delete.
        $this->dynamoDb->delete( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->shouldNotBeCalled();

        $this->dynamoDb->read( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->willReturn([
            'expires' => time() + 1000,
        ]);

        $result = $sm->read( self::TEST_SESSION_ID );

        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);

    }

}
