<?php
namespace AppTest\Validator;

use PHPUnit\Framework\TestCase;

use App\Validator\Csrf;

class CsrfTest extends TestCase
{

    public function testExceptionWithoutToken()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp( '/CSRF/' );

        $validator = new Csrf();
        $this->assertInstanceOf(Csrf::class, $validator);
    }

    public function testExceptionWithShortToken()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp( '/CSRF/' );

        $validator = new Csrf([
            'secret' => 'short-token'
        ]);

        $this->assertInstanceOf(Csrf::class, $validator);
    }

    public function testExceptionWithoutName()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/name/' );

        $validator = new Csrf([
            'secret' => bin2hex( random_bytes(32) )
        ]);

        $validator->getHash();
    }

    public function testWeGetExpectedHash()
    {
        $name = 'testing';
        $secret = bin2hex( random_bytes(32) );

        $validator = new Csrf([
            'name' => $name,
            'secret' => $secret
        ]);

        $hash = $validator->getHash();

        $this->assertEquals(hash('sha512', $secret.$name), $hash);
    }

    public function testWithIncorrectHash()
    {
        $validator = new Csrf([
            'name' => 'testing',
            'secret' => bin2hex( random_bytes(32) )
        ]);

        $this->assertFalse( $validator->isValid('invalid-hash') );
    }

    public function testWithCorrectHash()
    {
        $validator = new Csrf([
            'name' => 'testing',
            'secret' => bin2hex( random_bytes(32) )
        ]);

        $hash = $validator->getHash();

        $this->assertTrue( $validator->isValid($hash) );
    }

}