<?php
namespace AppTest\Validator;

use Prophecy\Argument;
use PHPUnit\Framework\TestCase;

use Zend\Validator\ValidatorInterface;

use App\Validator\AllowEmptyValidatorWrapper;

class AllowEmptyValidatorWrapperTest extends TestCase
{

    public function testCanInstantiate()
    {
        $wrappedValidator = $this->prophesize(ValidatorInterface::class);

        $validator = new AllowEmptyValidatorWrapper( $wrappedValidator->reveal() );
        $this->assertInstanceOf(AllowEmptyValidatorWrapper::class, $validator);
    }

    public function testValidationWithEmptyValue()
    {
        $wrappedValidator = $this->prophesize(ValidatorInterface::class);
        $wrappedValidator->isValid( Argument::any() )->shouldNotBeCalled();

        $validator = new AllowEmptyValidatorWrapper( $wrappedValidator->reveal() );

        $this->assertTrue( $validator->isValid('') );
    }

    public function testValidationWithNonEmptyValue()
    {
        $wrappedValidator = $this->prophesize(ValidatorInterface::class);
        $wrappedValidator->isValid( Argument::any() )->shouldBeCalled();
        $wrappedValidator->isValid( Argument::any() )->willReturn(false);

        $validator = new AllowEmptyValidatorWrapper( $wrappedValidator->reveal() );

        $this->assertFalse( $validator->isValid('a-test-value') );
    }

    public function testGetMessagesIsRelayed()
    {
        $wrappedValidator = $this->prophesize(ValidatorInterface::class);
        $wrappedValidator->getMessages()->shouldBeCalled();
        $wrappedValidator->getMessages()->willReturn([]);

        $validator = new AllowEmptyValidatorWrapper( $wrappedValidator->reveal() );

        $this->assertInternalType('array', $validator->getMessages());
    }

}
