<?php
namespace OpgTest\Refunds\Log\Writer;

use Opg\Refunds\Log\Writer\Sns;
use Aws\Sns\SnsClient;

use PHPUnit\Framework\TestCase;

class SnsTest extends TestCase
{

    public function testCanInstantiate()
    {
        $sns = new Sns(
            $this->prophesize(SnsClient::class)->reveal(), array()
        );
        $this->assertInstanceOf(Sns::class, $sns);
    }

}