<?php
namespace AppTest\Service\AssistedDigital;

use PHPUnit\Framework\TestCase;
use App\Service\AssistedDigital\LinkToken;

class LinkTokenTest extends TestCase
{
    private $testKey;

    protected function setUp()
    {
        $this->testKey = bin2hex(random_bytes(32));
    }

    public function testCanInstantiate()
    {
        $instance = new LinkToken($this->testKey);
        $this->assertInstanceOf(LinkToken::class, $instance);
    }

    public function testCannotInstantiateWithBadKey()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/Invalid key/' );

        $instance = new LinkToken(bin2hex(random_bytes(16)));
        $this->assertInstanceOf(LinkToken::class, $instance);
    }

    public function testCannotMakeTokenWithEmptyPayload()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/empty/' );

        //---

        $payload = [];

        $instance = new LinkToken($this->testKey);

        $instance->generate($payload);
    }

    /*
     * Tests both the generation and verification of a token
     */
    public function testTokenGenerationAndVerification()
    {
        $payload = [
            'userId' => random_int(1, PHP_INT_MAX)
        ];

        $instance = new LinkToken($this->testKey);

        $token = $instance->generate($payload);

        $this->assertInternalType('string', $token);

        //---

        $returnedPayload = $instance->verify($token);

        $this->assertEquals($payload, $returnedPayload);
    }
}