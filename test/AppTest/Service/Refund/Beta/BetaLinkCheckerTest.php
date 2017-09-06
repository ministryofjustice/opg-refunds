<?php
namespace AppTest\Service\Refund\Beta;

use PHPUnit\Framework\TestCase;

use Aws\DynamoDb\DynamoDbClient;

use App\Service\Refund\Beta\BetaLinkChecker;
use Prophecy\Argument;

class BetaLinkCheckerTest extends TestCase
{

    // /beta/1/4668796799/ocYkoL26fsDafvCpHWPv4YhnhkkouXFr4kvKRYNSeQsNCcJh2K4jDqtCL9QR9xFKR4R4vo3cAVcrVdubFWcGRe

    /*
     * These details make up a valid link. They cannot be changed independently.
     */
    const TEST_LINK_ID = 1;
    const TEST_LINK_EXPIRES = 4668796799; // Valid until 2117-12-12
    const TEST_LINK_SIGNATURE = 'ocYkoL26fsDafvCpHWPv4YhnhkkouXFr4kvKRYNSeQsNCcJh2K4jDqtCL9QR9xFKR4R4vo3cAVcrVdubFWcGRe';
    const TEST_LINK_SIGNATURE_KEY = 'a40cc15a3dae52c658001b9f506e0dc6c19ab667e212cdedaaad9b4b9ecd7d2f';

    //---

    private $dynamoDb;

    protected function setUp()
    {
        $this->dynamoDb = $this->prophesize(DynamoDbClient::class);
    }

    private function getBetaLinkChecker( bool $betaEnabled )
    {
        return new BetaLinkChecker(
            $this->dynamoDb->reveal(),
            ['table_name'=>'test_table'],
            self::TEST_LINK_SIGNATURE_KEY,
            $betaEnabled
        );
    }

    //---

    public function testCanInstantiate()
    {
        $checker = $this->getBetaLinkChecker(true);
        $this->assertInstanceOf(BetaLinkChecker::class, $checker);
    }

    public function testIsLinkValidWhenBetaDisabled()
    {
        $checker = $this->getBetaLinkChecker(false);

        $this->assertTrue(
            $checker->isLinkValid( null, null, null )
        );
    }

    public function testIsLinkValidWhenBetaEnabled()
    {
        $checker = $this->getBetaLinkChecker(true);

        $result = $checker->isLinkValid( null, null, null );
        $this->assertInternalType('string', $result);
        $this->assertEquals('missing-data', $result);

        //---

        $result = $checker->isLinkValid( self::TEST_LINK_ID, 123, self::TEST_LINK_SIGNATURE );
        $this->assertInternalType('string', $result);
        $this->assertEquals('expired', $result);

        //---

        $result = $checker->isLinkValid(
            self::TEST_LINK_ID,
            self::TEST_LINK_EXPIRES,
            // An valid but incorrect signature.
            'of0rWeGOZWahAD0cmyuLdLzMKpZFJuen2qPfhKgB84RsEsiqoQB4LHgR3xnu1uA0WW4qhEV5BWFIB4SjxylZht'
        );
        $this->assertInternalType('string', $result);
        $this->assertEquals('invalid-signature', $result);

        //---

        $result = $checker->isLinkValid(
            self::TEST_LINK_ID,
            self::TEST_LINK_EXPIRES,
            // An invalid signature.
            'fdgnfdsghfd843tneruigndf'
        );
        $this->assertInternalType('string', $result);
        $this->assertEquals('invalid-signature', $result);

        //---

        // Checks valid details
        $result = $checker->isLinkValid( self::TEST_LINK_ID, self::TEST_LINK_EXPIRES, self::TEST_LINK_SIGNATURE );
        $this->assertTrue( $result );

    }

    public function testHasLinkBeenUsedWhenBetaDisabled()
    {
        $checker = $this->getBetaLinkChecker(false);

        $this->assertFalse(
            $checker->hasLinkBeenUsed( self::TEST_LINK_ID )
        );
    }

    public function testHasLinkBeenUsedWhenBetaEnabled()
    {
        $checker = $this->getBetaLinkChecker(true);

        $this->dynamoDb->getItem( Argument::type('array') )->willReturn([ 'No-Item'=> true ]);

        $this->assertFalse(
            $checker->hasLinkBeenUsed( self::TEST_LINK_ID )
        );

        //---

        $this->dynamoDb->getItem( Argument::type('array') )->willReturn([ 'Item'=> true ]);

        $this->assertTrue(
            $checker->hasLinkBeenUsed( self::TEST_LINK_ID )
        );
    }

    public function testFlagLinkAsUsedWhenBetaDisabled()
    {
        $checker = $this->getBetaLinkChecker(false);

        $this->dynamoDb->putItem( Argument::type('array') )->shouldNotBeCalled();;

        $this->assertNull(
            $checker->flagLinkAsUsed( self::TEST_LINK_ID, 123 )
        );

    }

    public function testFlagLinkAsUsedWhenBetaEnabled()
    {
        $checker = $this->getBetaLinkChecker(true);

        $this->dynamoDb->putItem( Argument::type('array') )->shouldBeCalled();;

        $this->assertNull($checker->flagLinkAsUsed( self::TEST_LINK_ID, 123 ));
    }

    public function testFlagLinkAsUsedWhenBetaEnabledAndInvalidLinkId()
    {
        $checker = $this->getBetaLinkChecker(true);

        $this->expectException(\UnexpectedValueException::class);

        $checker->flagLinkAsUsed( 'not-a-number', 123 );
    }

}