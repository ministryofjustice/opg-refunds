<?php
namespace AppTest\Form;

use PHPUnit\Framework\TestCase;

use App\Form\DonorDetails;

// All DOB tests are now in Fieldset/DobTest.php

class DonorDetailsTest extends TestCase
{

    private function getForm()
    {
        return new DonorDetails([
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
            'address-1' => 'Line 1',
            'address-2' => 'Line 2',
            'address-3' => 'Line 3',
            'address-postcode' => 'SW4 4JQ',
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
        $this->assertInstanceOf(DonorDetails::class, $form);
    }

    public function testHasExpectedFields()
    {
        $form = $this->getForm();

        $elements = $form->getElements();

        $this->assertCount(13, $elements);
        $this->assertArrayHasKey( 'title', $elements);
        $this->assertArrayHasKey( 'first', $elements);
        $this->assertArrayHasKey( 'last', $elements);
        $this->assertArrayHasKey( 'address-1', $elements);
        $this->assertArrayHasKey( 'address-2', $elements);
        $this->assertArrayHasKey( 'address-3', $elements);
        $this->assertArrayHasKey( 'address-postcode', $elements);
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
    // Address Tests

    public function testMissingAddress1()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        unset($data['address-1']);

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );
    }

    public function testMissingAddress2()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        unset($data['address-2']);

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        // --- This is an optional field ---
        $this->assertTrue( $form->isValid() );
    }

    public function testMissingAddress3()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        unset($data['address-3']);

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        // --- This is an optional field ---
        $this->assertFalse( $form->isValid() );
    }

    public function testMissingAddressPostcode()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        unset($data['address-postcode']);

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );
    }

    //---------------------------------------------
    // DOB Tests

    public function testWithMissingDob()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        $data['dob'] = [
            'day' => '',
            'month' => '',
            'year' => '',
        ];

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );
    }

    //------------------------------------------------
    // All DOB tests are now in Fieldset/DobTest.php
    //------------------------------------------------

}