<?php
namespace AppTest\Form;

use PHPUnit\Framework\TestCase;

use DateTime;
use DateInterval;

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
    // DOB Tests

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

    public function testValidDobYearRange()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        $age18  = new DateTime('2017-04-01 -'.Dob::MIN_AGE.' years');
        $age120 = new DateTime('2017-04-01 -'.Dob::MAX_AGE.' years +1 day');

        $testDate = clone $age120;

        while ($testDate < $age18) {
            $data['dob']['year'] = $testDate->format('Y');
            $data['dob']['month'] = $testDate->format('m');
            $data['dob']['day'] = $testDate->format('d');

            $form->setData(
                ['secret' => $form->get('secret')->getValue()] + $data
            );

            $this->assertTrue( $form->isValid() );

            $testDate->add( new DateInterval('P1M') );
        }
    }

    public function testInvalidDobYearRange()
    {
        $form = $this->getForm();
        $data = $this->getValidData();

        $age18 = new DateTime('2017-04-01 -'.Dob::MIN_AGE.' years');

        $age18->add( new DateInterval('P1D') );

        $data['dob']['year'] = $age18->format('Y');
        $data['dob']['month'] = $age18->format('m');
        $data['dob']['day'] = $age18->format('d');

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );

        //---

        $age120 = new DateTime('2017-04-01 -'.Dob::MAX_AGE.' years');

        $age120->sub( new DateInterval('P1D') );

        $data['dob']['year'] = $age120->format('Y');
        $data['dob']['month'] = $age120->format('m');
        $data['dob']['day'] = $age120->format('d');


        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );
    }

    public function testWellFormattedButInvalidDate()
    {
        $form = $this->getForm( true );
        $data = $this->getValidData();

        $data['dob'] = [
            'day' => '30',
            'month' => '2',
            'year' => '1987',
        ];

        $form->setData(
            ['secret' => $form->get('secret')->getValue()] + $data
        );

        $this->assertFalse( $form->isValid() );
    }

}