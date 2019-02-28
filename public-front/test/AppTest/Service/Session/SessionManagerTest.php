<?php
namespace AppTest\Service\Session;

use PHPUnit\Framework\TestCase;
use App\Service\Session\SessionManager;
use Aws\DynamoDb\SessionConnectionInterface as DynamoDbSessionConnectionInterface;

class SessionManagerTest extends TestCase
{

    const TEST_SESSION_ID = 'test-session-id';

    const HASH_ALGORITHM = 'sha256';

    private $dynamoDb;

    protected function setUp()
    {

        $this->dynamoDb = $this->prophesize(DynamoDbSessionConnectionInterface::class);

    }

    private function getSessionManager(){
        return new SessionManager( $this->dynamoDb->reveal() );
    }

    public function testCanInstantiate()
    {

        $sm = new SessionManager( $this->dynamoDb->reveal() );
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

        $sm = $this->getSessionManager();
        $this->assertInstanceOf(SessionManager::class, $sm);

        $sm->delete( self::TEST_SESSION_ID );
    }

    public function testCanWriteWithNewData()
    {
        $data = [ 'test'=>true ];

        $expected = base64_encode(gzdeflate(json_encode($data)));

        $sm = $this->getSessionManager();

        // We expect a base64 version of that data to be saved to DynamoDD
        $this->dynamoDb->write( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ), $expected, true )->shouldBeCalled();

        $sm->write( self::TEST_SESSION_ID, $data );
    }


    public function testReadDataWhenExpired()
    {

        $sm = $this->getSessionManager();

        $this->dynamoDb->read( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->shouldBeCalled();

        // We expect delete() to be called when we find an expired row.
        $this->dynamoDb->delete( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->shouldBeCalled();

        $this->dynamoDb->read( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->willReturn([
            'expires' => 123,
            'data' => 'data'
        ]);

        $result = $sm->read( self::TEST_SESSION_ID );

        // We expect false when expired
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }


    public function testReadDataWhenEmpty()
    {

        $sm = $this->getSessionManager();

        // As the data is just empty, not expired, we should not see a delete.
        $this->dynamoDb->delete( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->shouldNotBeCalled();

        $this->dynamoDb->read( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->willReturn([
            'expires' => time() + 1000,
        ]);

        $result = $sm->read( self::TEST_SESSION_ID );

        // We expect false when no data is set (even when there was metadata).
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }



    public function testCanReadExistingData()
    {
        $data = [ 'test' => true ];

        $expected = base64_encode(gzdeflate(json_encode($data)));

        $sm = $this->getSessionManager();

        $this->dynamoDb->read( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->shouldBeCalled();

        $this->dynamoDb->read( hash( self::HASH_ALGORITHM, self::TEST_SESSION_ID ) )->willReturn([
            'expires' => time() + 1000,
            'data' => $expected
        ]);

        $result = $sm->read( self::TEST_SESSION_ID );

        $this->assertInternalType('array', $result);
        $this->assertEquals($data, $result);
    }


}
