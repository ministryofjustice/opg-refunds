<?php
namespace AppTest\Service\Refund\Beta;

use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;

use App\Service\Refund\Beta\BetaLinkChecker;
use App\Service\Refund\Beta\BetaLinkCheckerFactory;

class BetaLinkCheckerFactoryTest extends TestCase
{

    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

    }


    private function getConfigArray( array $fields )
    {
        $details = array_intersect_key([
            'enabled' => true,
            'dynamodb' => [
                'client' => [
                    'version' => '2012-08-10',
                    'region' => 'eu-west-2',
                ],
                'settings' => array(),
            ],
            'link' => [
                'signature' => [
                    'key' => 'a40cc15a3dae52c658001b9f506e0dc6c19ab667e212cdedaaad9b4b9ecd7d2f',
                ]
            ],
        ], array_flip($fields));

        return [ 'beta' => $details ];
    }

    //---

    public function testCanInstantiate()
    {
        $factory = new BetaLinkCheckerFactory( $this->container->reveal() );
        $this->assertInstanceOf(BetaLinkCheckerFactory::class, $factory);
    }

    //---

    public function testFactoryWithEmptyConfig()
    {
        $factory = new BetaLinkCheckerFactory( $this->container->reveal() );

        $this->container->get( 'config' )->willReturn( [] );

        $this->expectException(\UnexpectedValueException::class);

        $factory($this->container->reveal());
    }

    public function testFactoryWithoutEnabledConfig()
    {
        $factory = new BetaLinkCheckerFactory( $this->container->reveal() );

        $this->container->get( 'config' )->willReturn( $this->getConfigArray([]) );

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/enabled/' );

        $factory($this->container->reveal());
    }

    public function testFactoryWithoutSignatureConfig()
    {
        $factory = new BetaLinkCheckerFactory( $this->container->reveal() );

        $this->container->get( 'config' )->willReturn( $this->getConfigArray(['enabled']) );

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/Signature/' );

        $factory($this->container->reveal());
    }

    public function testFactoryWithoutDynamoConfig()
    {
        $factory = new BetaLinkCheckerFactory( $this->container->reveal() );

        $this->container->get( 'config' )->willReturn( $this->getConfigArray(['enabled','link']) );

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/Dynamo DB/' );

        $factory($this->container->reveal());
    }

    public function testFactoryWithoutDynamoSettingsConfig()
    {
        $factory = new BetaLinkCheckerFactory( $this->container->reveal() );

        $config = $this->getConfigArray(['enabled','dynamodb','link']);
        unset($config['beta']['dynamodb']['settings']);
        $this->container->get( 'config' )->willReturn( $config );

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/Dynamo DB/' );

        $factory($this->container->reveal());
    }

    public function testFactoryWithoutDynamoClientConfig()
    {
        $factory = new BetaLinkCheckerFactory( $this->container->reveal() );

        $config = $this->getConfigArray(['enabled','dynamodb','link']);
        unset($config['beta']['dynamodb']['client']);
        $this->container->get( 'config' )->willReturn( $config );

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/Dynamo DB/' );

        $factory($this->container->reveal());
    }


    public function testFactoryWithConfig()
    {
        $factory = new BetaLinkCheckerFactory( $this->container->reveal() );

        $this->container->get( 'config' )->willReturn( $this->getConfigArray(['enabled','dynamodb','link']) );

        $result = $factory($this->container->reveal());

        $this->assertInstanceOf(BetaLinkChecker::class, $result);
    }

}