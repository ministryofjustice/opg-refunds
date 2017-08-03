<?php
namespace AppTest\Crypt;

use App\Crypt\Hybrid;
use Zend\Crypt\PublicKey\Rsa;
use Zend\Crypt\PublicKey\RsaOptions;
use PHPUnit\Framework\TestCase;

class HybridTest extends TestCase
{

    const TEST_STRING = 'text to encrypt';

    public function testCanInstantiate()
    {
        $cipher = new Hybrid();
        $this->assertInstanceOf(Hybrid::class, $cipher);
    }

    /**
     * This should thrown an exception as no valid key is passed.
     */
    public function testWhenNoKeySet()
    {
        $cipher = new Hybrid(
            null, // Use default BlockCipher
            Rsa::factory([])
        );

        //---

        $this->expectException(\Zend\Crypt\Exception\RuntimeException::class);

        $cipher->encrypt(self::TEST_STRING);
    }

    //-------------------

    public function testEncWhenKeyIsPassedAsParam()
    {
        $options = (new RsaOptions)->generateKeys();

        $cipher = new Hybrid(
            null, // Use default BlockCipher
            Rsa::factory([])
        );

        //---

        $cipherText = $cipher->encrypt(self::TEST_STRING, $options->getPublicKey());

        $this->assertInternalType('string', $cipherText);

        return [
            'options' => $options,
            'cipherText' => $cipherText,
        ];
    }

    /**
     * @depends testEncWhenKeyIsPassedAsParam
     */
    public function testDecWhenKeyIsPassedAsParam(array $input)
    {
        $cipher = new Hybrid(
            null, // Use default BlockCipher
            Rsa::factory([])
        );

        $text = $cipher->decrypt($input['cipherText'], $input['options']->getPrivateKey());

        $this->assertInternalType('string', $text);
        $this->assertSame(self::TEST_STRING, $text);
    }

    //-------------------

    public function testEncWhenKeyIsSetInFactory()
    {
        $options = (new RsaOptions)->generateKeys();

        $cipher = new Hybrid(
            null, // Use default BlockCipher
            Rsa::factory(['public_key'=>(string)$options->getPublicKey()])
        );

        //---

        $cipherText = $cipher->encrypt(self::TEST_STRING );

        $this->assertInternalType('string', $cipherText);

        return [
            'options' => $options,
            'cipherText' => $cipherText,
        ];
    }

    /**
     * @depends testEncWhenKeyIsSetInFactory
     */
    public function testDecWhenKeyIsSetInFactory(array $input)
    {

        $cipher = new Hybrid(
            null, // Use default BlockCipher
            Rsa::factory(['private_key'=>(string)$input['options']->getPrivateKey()])
        );

        $text = $cipher->decrypt($input['cipherText']);

        $this->assertInternalType('string', $text);
        $this->assertSame(self::TEST_STRING, $text);
    }

}