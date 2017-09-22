<?php
namespace AppTest\Form;

use PHPUnit\Framework\TestCase;

use DateTime;
use DateInterval;

use App\Form\ActorDetails;
use App\Form\Fieldset\Dob;

// All DOB tests are now in Fieldset/DobTest.php

class ActorDetailsTest extends TestCase
{

    private function getForm()
    {
        return new ActorDetails([
            'csrf' => bin2hex(random_bytes(32))
        ]);
    }

    private function getValidData()
    {
        return [
            'title' => 'Ms',
            'first' => 'Betty',
            'last' => 'Jones',
            'poa-title' => 'Sir',
            'poa-first' => 'Fred',
            'poa-last' => 'Jones',
            'dob' => [
                'day' => '1',
                'month' => '1',
                'year' => date('Y') - 20,
            ]
        ];
    }

    //-----------

    public function testCanInstantiate()
    {
        $form = $this->getForm();
        $this->assertInstanceOf(ActorDetails::class, $form);
    }

    public function testHasExpectedFields()
    {
        $form = $this->getForm();

        $elements = $form->getElements();

        $this->assertCount(8, $elements);
        $this->assertArrayHasKey( 'title', $elements);
        $this->assertArrayHasKey( 'first', $elements);
        $this->assertArrayHasKey( 'last', $elements);
        $this->assertArrayHasKey( 'poa-title', $elements);
        $this->assertArrayHasKey( 'poa-first', $elements);
        $this->assertArrayHasKey( 'poa-last', $elements);
        $this->assertArrayHasKey( 'secret', $elements);

        //---

        $fieldsets = $form->getFieldsets();

        $this->assertCount(1, $fieldsets);
        $this->assertArrayHasKey( 'dob', $fieldsets);

        //---

        $elements = $fieldsets['dob']->getElements();

        $this->assertCount(3, $elements);
        $this->assertArrayHasKey( 'day', $elements);
        $this->assertArrayHasKey( 'month', $elements);
        $this->assertArrayHasKey( 'year', $elements);
    }

    public function testAllFieldsPresentAndValid()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertTrue( $form->isValid() );
    }

    //---------------------------------------------
    // Names Tests

    public function testMissingTitle()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        unset($data['title']);

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );
    }

    public function testMissingFirstName()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        unset($data['first']);

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );
    }

    public function testMissingLastName()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        unset($data['last']);

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );
    }

    //---------------------------------------------
    // Optional Names Tests

    public function testAllFieldsWithoutOptionalNamePresentAndValid()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        unset($data['poa-title']);
        unset($data['poa-first']);
        unset($data['poa-last']);

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );

        //---

        // Filter out the optional fields.

        $fieldsToValidate = array_flip(array_diff_key(
            array_flip(array_keys($form->getElements() + $form->getFieldsets())),
            // Remove the fields below from the validator.
            array_flip(['poa-title', 'poa-first', 'poa-last'])
        ));

        $form->setValidationGroup($fieldsToValidate);

        //---

        $this->assertTrue( $form->isValid() );
    }

    //---------------------------------------------
    // DOB Tests

    //------------------------------------------------
    // All DOB tests are now in Fieldset/DobTest.php
    //------------------------------------------------

}