<?php
namespace AppTest\Form;

use PHPUnit\Framework\TestCase;

use App\Form\Csrf;

class CsrfTest extends TestCase
{

    private function getForm()
    {
        return new Csrf([
            'csrf' => bin2hex(random_bytes(32))
        ]);
    }

    //-----------

    public function testCanInstantiate()
    {
        $form = $this->getForm();
        $this->assertInstanceOf(Csrf::class, $form);
    }

    public function testHasExpectedField()
    {
        $form = $this->getForm();

        $elements = $form->getElements();

        $this->assertInternalType('array', $elements);
        $this->assertArrayHasKey( 'secret', $elements);
    }

    public function testFormIsValidWhenExpected()
    {
        $form = $this->getForm();

        $form->setData([
            'secret' => $form->get('secret')->getValue()
        ]);

        $this->assertTrue( $form->isValid() );
    }

    public function testFormIsInvalidWhenCsrfMissing()
    {
        $form = $this->getForm();

        $form->setData([]);

        $this->assertFalse( $form->isValid() );

        $this->assertArrayHasKey( 'secret', $form->getMessages());
        $this->assertArrayHasKey( 'isEmpty', $form->getMessages()['secret']);

        $this->assertSame('required', $form->getMessages()['secret']['isEmpty']);
    }

    public function testFormIsInvalidWhenCsrfIncorrect()
    {
        $form = $this->getForm();

        $form->setData([
            'secret' => bin2hex(random_bytes(32))
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArrayHasKey( 'secret', $form->getMessages());
        $this->assertArrayHasKey( 'notSame', $form->getMessages()['secret']);

        $this->assertSame('csrf', $form->getMessages()['secret']['notSame']);
    }

}
