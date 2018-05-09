<?php
namespace AppTest\Service\Refund\Data;

use PHPUnit\Framework\TestCase;

use App\Service\Refund\Data\PhoneNumber;

use App\Service\Refund\Beta\BetaLinkChecker;
use Prophecy\Argument;

class PhoneNumberTest extends TestCase
{

    public function testCanInstantiate()
    {
        $phoneNumber = new PhoneNumber('07834012321');
        $this->assertInstanceOf(PhoneNumber::class, $phoneNumber);
    }

    public function testUkMobile()
    {
        $number = '07834012321';

        $phoneNumber = new PhoneNumber($number);

        $this->assertTrue($phoneNumber->isMobile());
        $this->assertEquals($number, $phoneNumber->get());
    }

    public function testUkMobileWithIntCode()
    {
        $number = '7834012321';

        $phoneNumber = new PhoneNumber('+44' . $number);

        $this->assertTrue($phoneNumber->isMobile());
        $this->assertEquals('0' . $number, $phoneNumber->get());
    }

    public function testUkLandline()
    {
        $number = '02033343555';

        $phoneNumber = new PhoneNumber($number);

        $this->assertFalse($phoneNumber->isMobile());
        $this->assertEquals($number, $phoneNumber->get());
    }

    public function testUkLandlineIntCode()
    {
        $number = '2033343555';

        $phoneNumber = new PhoneNumber('+44' . $number);

        $this->assertFalse($phoneNumber->isMobile());
        $this->assertEquals('0' . $number, $phoneNumber->get());
    }

    public function testInternationalNumber()
    {
        $number = '+34932457900';

        $phoneNumber = new PhoneNumber($number);

        $this->assertFalse($phoneNumber->isMobile());
        $this->assertEquals($number, $phoneNumber->get());
    }
}