<?php
namespace AppTest\Form\Fieldset;

use PHPUnit\Framework\TestCase;

use DateTime;
use DateInterval;

use Laminas\Form\Form as LaminasForm;
use Laminas\InputFilter\InputFilter;

use App\Form\Fieldset\Dob;

class DobTest extends TestCase
{

    /**
     * Returns a Dob instance, wrapped ina Form, so it can be validated.
     * Sets up the fieldset as per App\Form\ActorDetails
     */
    private function getDobWrappedInForm() : LaminasForm
    {
        $form = new LaminasForm;

        $inputFilter = new InputFilter;
        $form->setInputFilter($inputFilter);

        $dob = new Dob;

        $form->add($dob);
        $inputFilter->add($dob->getInputFilter(), 'dob');

        return $form;
    }

    public function testCanInstantiate()
    {
        $field = new Dob;
        $this->assertInstanceOf(Dob::class, $field);
    }

    //------------------------------------------
    // Test a valid date

    public function testWhenValidYearIsSet()
    {
        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '5',
                'month' => '10',
                'year' => '1995'
            ]
        ]);

        $this->assertTrue( $form->isValid() );
    }

    //------------------------------------------
    // Test when bits of the date are missing

    public function testWhenEmpty()
    {
        $form = $this->getDobWrappedInForm();

        $form->setData([]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'isEmpty' => 'required' ] ],
            $form->getMessages()
        );
    }

    public function testWhenAllZero()
    {
        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '0',
                'month' => '0',
                'year' => '0'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'isEmpty' => 'required' ] ],
            $form->getMessages()
        );
    }

    public function testWhenOnlyDaySet()
    {
        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '5'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'isEmpty' => 'required' ] ],
            $form->getMessages()
        );
    }

    public function testWhenOnlyMonthSet()
    {
        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'month' => '5'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'isEmpty' => 'required' ] ],
            $form->getMessages()
        );
    }

    public function testWhenOnlyYearSet()
    {
        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'year' => '1995'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'isEmpty' => 'required' ] ],
            $form->getMessages()
        );
    }

    //------------------------------------------
    // Test when invalid dates are passed

    public function testWithInvalidDay()
    {
        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '0',
                'month' => '10',
                'year' => '1995'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'isEmpty' => 'required' ] ],
            $form->getMessages()
        );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '32',
                'month' => '10',
                'year' => '1995'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'callbackValue' => 'invalid-date' ] ],
            $form->getMessages()
        );
    }

    public function testWithInvalidMonth()
    {
        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '5',
                'month' => '0',
                'year' => '1995'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'isEmpty' => 'required' ] ],
            $form->getMessages()
        );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '5',
                'month' => '13',
                'year' => '1995'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'callbackValue' => 'invalid-date' ] ],
            $form->getMessages()
        );
    }

    public function testWithInvalidYear()
    {
        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '5',
                'month' => '5',
                'year' => '0'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'isEmpty' => 'required' ] ],
            $form->getMessages()
        );
    }

    //------------------------------------------
    // Test when too young

    public function testTooYoung()
    {
        $form = $this->getDobWrappedInForm();

        $born = new DateTime('now -'.Dob::MIN_AGE.' years');

        $born->add( new DateInterval('P1D') );

        $form->setData([
            'dob' => [
                'day' => $born->format('d'),
                'month' => $born->format('m'),
                'year' => $born->format('Y')
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'callbackValue' => 'too-young' ] ],
            $form->getMessages()
        );
    }

    //------------------------------------------
    // Test when too old

    public function testTooOld()
    {
        $form = $this->getDobWrappedInForm();

        $born = new DateTime('now -'.Dob::MAX_AGE.' years');

        $born->sub( new DateInterval('P1D') );

        $form->setData([
            'dob' => [
                'day' => $born->format('d'),
                'month' => $born->format('m'),
                'year' => $born->format('Y')
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'callbackValue' => 'too-old' ] ],
            $form->getMessages()
        );
    }

    //------------------------------------------
    // Test future date

    public function testFutureDate()
    {
        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '5',
                'month' => '5',
                'year' => (date('Y') + 1)
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'callbackValue' => 'future-date' ] ],
            $form->getMessages()
        );
    }

    //------------------------------------------
    // Tests a large range of valid dates

    public function testValidDobYearRange()
    {
        $form = $this->getDobWrappedInForm();

        $age18  = new DateTime('now -'.Dob::MIN_AGE.' years');
        $age120 = new DateTime('now -'.Dob::MAX_AGE.' years +1 day');

        $testDate = clone $age120;

        while ($testDate < $age18) {
            $form->setData([
                'dob' => [
                    'day' => $testDate->format('d'),
                    'month' => $testDate->format('m'),
                    'year' => $testDate->format('Y')
                ]
            ]);

            $this->assertTrue( $form->isValid() );

            $testDate->add( new DateInterval('P1M') );
        }
    }

    //------------------------------------------
    // Test strange cases found

    /*
     * Strange response found by QA testers.
     * Tests here check fixers work as expected.
     */

    public function testStrangeDates()
    {
        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '',
                'month' => '',
                'year' => ''
            ]
        ]);

        // Empty strings should fail

        $this->assertFalse( $form->isValid() );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '11s',
                'month' => '12s',
                'year' => '1983s'
            ]
        ]);

        // We don't allow a letter in a date

        $this->assertFalse( $form->isValid() );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => ' 11',
                'month' => ' 12',
                'year' => ' 1983'
            ]
        ]);

        // We should allow blank spaces before values.

        $this->assertTrue( $form->isValid() );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '11 ',
                'month' => '12 ',
                'year' => '1983 '
            ]
        ]);

        // We should allow blank spaces after values.

        $this->assertTrue( $form->isValid() );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '1',
                'month' => '1',
                'year' => '19'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        // Gave future-date
        $this->assertArraySubset(
            [ 'dob' => [ 'callbackValue' => 'too-old' ] ],
            $form->getMessages()
        );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '1',
                'month' => '1',
                'year' => '12'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        // Gave too-young
        $this->assertArraySubset(
            [ 'dob' => [ 'callbackValue' => 'too-old' ] ],
            $form->getMessages()
        );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '1',
                'month' => '1',
                'year' => '1'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        // Gave too-young
        $this->assertArraySubset(
            [ 'dob' => [ 'callbackValue' => 'too-old' ] ],
            $form->getMessages()
        );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '1',
                'month' => '1',
                'year' => '11111'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        // Gave too-young
        $this->assertArraySubset(
            [ 'dob' => [ 'callbackValue' => 'invalid-date' ] ],
            $form->getMessages()
        );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '1',
                'month' => '1',
                'year' => '0001'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        $this->assertArraySubset(
            [ 'dob' => [ 'callbackValue' => 'too-old' ] ],
            $form->getMessages()
        );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '1',
                'month' => '1',
                'year' => '00011'
            ]
        ]);

        $this->assertFalse( $form->isValid() );

        // Gave too-young
        $this->assertArraySubset(
            [ 'dob' => [ 'callbackValue' => 'too-old' ] ],
            $form->getMessages()
        );

        //---

        $form = $this->getDobWrappedInForm();

        $form->setData([
            'dob' => [
                'day' => '001',
                'month' => '2',
                'year' => '1983'
            ]
        ]);

        // This was throwing a PHP error.

        $this->assertTrue( $form->isValid() );
    }
}