<?php
namespace AppTest\Form;

use PHPUnit\Framework\TestCase;

use App\Form\ContactDetails;

class ContactDetailsTest extends TestCase
{

    private function getForm()
    {
        return new ContactDetails([
            'csrf' => bin2hex( random_bytes(32) )
        ]);
    }

    //-----------

    public function testCanInstantiate()
    {
        $form = $this->getForm();
        $this->assertInstanceOf(ContactDetails::class, $form);
    }


    public function testHasExpectedFields()
    {
        $form = $this->getForm();

        $elements = $form->getElements();

        $this->assertCount(5, $elements);
        $this->assertArrayHasKey( 'email', $elements);
        $this->assertArrayHasKey( 'phone', $elements);
        $this->assertArrayHasKey( 'secret', $elements);
        $this->assertArrayHasKey( 'receive-notifications', $elements);
    }

    public function testFilters()
    {
        $form = $this->getForm();

        $form->setData([
            'phone' => ' 07635 860 432 ',
            'email' => ' test@eXample.com ',
            'secret' => $form->get('secret')->getValue()
        ]);

        $form->isValid();

        $values = $form->getData();

        $this->assertEquals( '07635860432', $values['phone'] );
        $this->assertEquals( 'test@eXample.com', $values['email'] );
    }

    public function testValidationWhenTogether()
    {
        $form = $this->getForm();

        $form->setData([
            'phone' => '07635860432',
            'email' => 'test@example.com',
            'secret' => $form->get('secret')->getValue(),
            'receive-notifications' => 'yes'
        ]);

        $this->assertTrue( $form->isValid() );

        //---

        $form = $this->getForm();

        $form->setData([
            'phone' => '0763586043d',
            'email' => 'test@example.com',
            'secret' => $form->get('secret')->getValue(),
            'receive-notifications' => 'yes'
        ]);

        $this->assertFalse( $form->isValid() );

        //---

        $form = $this->getForm();

        $form->setData([
            'phone' => '07635860437',
            'email' => 'test-example.com',
            'secret' => $form->get('secret')->getValue(),
            'receive-notifications' => 'yes'
        ]);

        $this->assertFalse( $form->isValid() );

        //--

        $form->setData([
            'phone' => '07635860432',
            'email' => 'test@example.com',
            'secret' => $form->get('secret')->getValue(),
            'receive-notifications' => 'invalid-value'
        ]);

        $this->assertFalse( $form->isValid() );
    }


    public function testCsrfValidation()
    {

        // Test false with an incorrect secret

        $form = $this->getForm();

        $form->setData([
            'phone' => '07635860432',
            'email' => 'test@example.com',
            'secret' => 'incorrect-secret',
            'receive-notifications' => 'yes'
        ]);

        $this->assertFalse( $form->isValid() );

        //---

        // Test false with no secret

        $form = $this->getForm();

        $form->setData([
            'phone' => '07635860432',
            'email' => 'test@example.com',
            'receive-notifications' => 'yes'
        ]);

        $this->assertFalse( $form->isValid() );
    }

    public function testOneFieldRequired()
    {
        $form = $this->getForm();

        $form->setData([]);

        $this->assertFalse( $form->isValid() );

        //---

        $form = $this->getForm();

        $form->setData([
            'phone' => '',
            'secret' => $form->get('secret')->getValue(),
            'receive-notifications' => 'yes'
        ]);

        $this->assertFalse( $form->isValid() );

        //---

        $form = $this->getForm();

        $form->setData([
            'email' => '',
            'secret' => $form->get('secret')->getValue(),
            'receive-notifications' => 'yes'
        ]);

        $this->assertFalse( $form->isValid() );

        //---

        $form = $this->getForm();

        $form->setData([
            'email' => '',
            'phone' => '',
            'secret' => $form->get('secret')->getValue(),
            'receive-notifications' => 'yes'
        ]);

        $this->assertFalse( $form->isValid() );
    }

}
