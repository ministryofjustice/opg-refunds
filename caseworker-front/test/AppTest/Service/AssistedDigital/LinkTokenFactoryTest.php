<?php
namespace AppTest\Service\Refund\AssistedDigital;

use PHPUnit\Framework\TestCase;

use Interop\Container\ContainerInterface;

use App\Service\AssistedDigital\LinkToken;
use App\Service\AssistedDigital\LinkTokenFactory;

class LinkTokenFactoryTest extends TestCase
{
    protected $container;

    //---

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testCanInstantiate()
    {
        $factory = new LinkTokenFactory();
        $this->assertInstanceOf(LinkTokenFactory::class, $factory);
    }

    public function testFailsWhenNoKeySet()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/not configured/' );

        $factory = new LinkTokenFactory();

        $factory($this->container->reveal());
    }

    public function testFailsWhenBadKeySet()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/Invalid key/' );

        //---

        $this->container->get( 'config' )->willReturn([
            'ad' => [
                'link' => [
                    'signature' => [
                        'key' => bin2hex(random_bytes(16))
                    ]
                ],
            ]
        ]);

        //---

        $factory = new LinkTokenFactory();

        $factory($this->container->reveal());
    }

    public function testWithValidConfig()
    {
        $this->container->get( 'config' )->willReturn([
            'ad' => [
                'link' => [
                    'signature' => [
                        'key' => bin2hex(random_bytes(32))
                    ]
                ],
            ]
        ]);

        //---

        $factory = new LinkTokenFactory();

        $instance = $factory($this->container->reveal());

        $this->assertInstanceOf(LinkToken::class, $instance);
    }
}