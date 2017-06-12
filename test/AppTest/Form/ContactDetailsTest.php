<?php
namespace AppTest\Form;

use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;

use App\Form\ContactDetails;

class ContactDetailsTest extends TestCase
{

    public function testCanInstantiate()
    {
        $form = new ContactDetails();
        $this->assertInstanceOf(ContactDetails::class, $form);
    }


    public function testHasExpectedFields()
    {
        $form = new ContactDetails();

        $elements = $form->getElements();

        $this->assertCount(2, $elements);
        $this->assertArrayHasKey( 'email', $elements);
        $this->assertArrayHasKey( 'mobile', $elements);
    }

    public function testFilters()
    {
        $form = new ContactDetails();

        $form->setData([
            'mobile' => ' 07635 860 432 ',
            'email' => ' test@eXample.com ',
        ]);

        $form->isValid();

        $values = $form->getData();

        $this->assertEquals( '07635860432', $values['mobile'] );
        $this->assertEquals( 'test@example.com', $values['email'] );
    }

    public function testEmailIsValidated()
    {

        $form = new ContactDetails();

        $form->setData([
            'email' => 'test@example.com',
            'mobile' => ''
        ]);

        $this->assertTrue( $form->isValid() );

        //---

        $form = new ContactDetails();

        $form->setData([
            'email' => 'not-an-email',
            'mobile' => ''
        ]);

        $this->assertFalse( $form->isValid() );
    }

    public function testMobileIsValidated()
    {
        $form = new ContactDetails();

        $form->setData([
            'mobile' => '07635860432',
            'email' => ''
        ]);

        $this->assertTrue( $form->isValid() );

        //---

        $form = new ContactDetails();

        $form->setData([
            'mobile' => 'not-a-mobile',
            'email' => ''
        ]);

        $this->assertFalse( $form->isValid() );
    }

    public function testValidationWhenTogether()
    {
        $form = new ContactDetails();

        $form->setData([
            'mobile' => '07635860432',
            'email' => 'test@example.com'
        ]);

        $this->assertTrue( $form->isValid() );

        //---

        $form = new ContactDetails();

        $form->setData([
            'mobile' => '0763586043d',
            'email' => 'test@example.com'
        ]);

        $this->assertFalse( $form->isValid() );

        //---

        $form = new ContactDetails();

        $form->setData([
            'mobile' => '07635860437',
            'email' => 'test-example.com'
        ]);

        $this->assertFalse( $form->isValid() );
    }

    public function testOneFieldRequired()
    {
        $form = new ContactDetails();

        $form->setData([]);

        $this->assertFalse( $form->isValid() );

        //---

        $form = new ContactDetails();

        $form->setData(['mobile' => '']);

        $this->assertFalse( $form->isValid() );

        //---

        $form = new ContactDetails();

        $form->setData(['email' => '']);

        $this->assertFalse( $form->isValid() );

        //---

        $form = new ContactDetails();

        $form->setData(['email' => '','mobile' => '']);

        $this->assertFalse( $form->isValid() );
    }

}
