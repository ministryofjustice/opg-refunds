<?php
namespace AppTest\Service\Session;

use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;

use App\Service\Session\SessionManager;
use App\Service\Session\SessionManagerFactory;

class SessionManagerFactoryTest extends TestCase
{

    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

    }

    private function getConfigArray( array $fields )
    {
        $details = array_intersect_key([
            'ttl' => 3600,
            'dynamodb' => [
                'client' => [
                    'version' => '2012-08-10',
                    'region' => 'eu-west-2',
                ],
                'settings' => array(),
            ],
        ], array_flip($fields));

        return [ 'session' => $details ];
    }

    public function testFactoryWithoutTtlConfigured()
    {

        $factory = new SessionManagerFactory();
        $this->assertInstanceOf(SessionManagerFactory::class, $factory);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/TTL/' );

        $factory($this->container->reveal());
    }


    public function testFactoryWithoutDynamoDbClientConfigured()
    {

        $factory = new SessionManagerFactory();
        $this->assertInstanceOf(SessionManagerFactory::class, $factory);

        $this->container->get( 'config' )->willReturn( $this->getConfigArray(['ttl']) );

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/Dynamo DB/' );

        $factory($this->container->reveal());
    }


    public function testFactoryWithFullConfiguration()
    {

        $factory = new SessionManagerFactory();
        $this->assertInstanceOf(SessionManagerFactory::class, $factory);

        $config = $this->getConfigArray(['ttl', 'dynamodb', 'encryption']);
        $config['session']['encryption']['keys'] = '1:'.bin2hex(random_bytes(32));

        $this->container->get( 'config' )->willReturn( $config );

        $sessionManager = $factory($this->container->reveal());

        $this->assertInstanceOf(SessionManager::class, $sessionManager);
    }
}
