<?php
namespace AppTest\Form;

use PHPUnit\Framework\TestCase;

use App\Form\ActorDetails;
use App\Form\Fieldset\Dob;

class ActorDetailsTest extends TestCase
{

    private function getForm($dobOptional = false)
    {
        return new ActorDetails([
            'csrf' => bin2hex(random_bytes(32)),
            'dob-optional' => $dobOptional
        ]);
    }

    private function getValidData()
    {
        return [
            'title' => 'Ms',
            'first' => 'Betty',
            'last' => 'Jones',
            'dob' => [
                'day' => '1',
                'month' => '2',
                'year' => date('Y'),
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

        $this->assertCount(4, $elements);
        $this->assertArrayHasKey( 'title', $elements);
        $this->assertArrayHasKey( 'first', $elements);
        $this->assertArrayHasKey( 'last', $elements);
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
    // DOB Tests

    public function testDobCanBeSetWhenOptional()
    {
        $form = $this->getForm( true );
        $data = $this->getValidData();

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertTrue( $form->isValid() );
    }

    public function testDobCanBeMissingWhenOptional()
    {
        $form = $this->getForm( true );
        $data = $this->getValidData();

        $data['dob'] = [
            'day' => '',
            'month' => '',
            'year' => '',
        ];

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertTrue( $form->isValid() );
    }

    public function testDobCannotJustBeDayWhenMissing()
    {
        $form = $this->getForm( true );
        $data = $this->getValidData();

        $data['dob'] = [
            'day' => '1',
            'month' => '',
            'year' => '',
        ];

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );
    }

    public function testDobCannotJustBeMonthWhenMissing()
    {
        $form = $this->getForm( true );
        $data = $this->getValidData();

        $data['dob'] = [
            'day' => '',
            'month' => '2',
            'year' => '',
        ];

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );
    }

    public function testDobCannotJustBeYearWhenMissing()
    {
        $form = $this->getForm( true );
        $data = $this->getValidData();

        $data['dob'] = [
            'day' => '',
            'month' => '',
            'year' => date('Y'),
        ];

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );
    }

    public function testDobDayRange()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        for ($i = 0; $i <= 32; $i++) {
            $data['dob']['day'] = $i;

            $form->setData(
                ['secret' => $form->get('secret')->getValue()] + $data
            );

            if ($i > 0 && $i < 32) {
                $this->assertTrue( $form->isValid() );
            } else {
                $this->assertFalse( $form->isValid() );
            }
        }
    }

    public function testDobMonthRange()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        for ($i = 0; $i <= 13; $i++) {
            $data['dob']['month'] = $i;

            $form->setData(
                ['secret' => $form->get('secret')->getValue()] + $data
            );

            if ($i > 0 && $i < 13) {
                $this->assertTrue( $form->isValid() );
            } else {
                $this->assertFalse( $form->isValid() );
            }
        }
    }

    public function testDobYearRange()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        $minYear = (int)date('Y') - Dob::MAX_AGE;
        $maxYear = (int)date('Y');

        for ($i = $minYear-1; $i <= $maxYear+1; $i++) {
            $data['dob']['year'] = $i;

            $form->setData(
                ['secret' => $form->get('secret')->getValue()] + $data
            );

            if ($i > $minYear-1 && $i < $maxYear+1) {
                $this->assertTrue( $form->isValid() );
            } else {
                $this->assertFalse( $form->isValid() );
            }
        }
    }

}