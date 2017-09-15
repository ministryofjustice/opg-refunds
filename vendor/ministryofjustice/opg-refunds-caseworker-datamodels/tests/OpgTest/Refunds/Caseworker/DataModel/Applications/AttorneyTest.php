<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\Attorney;
use Opg\Refunds\Caseworker\DataModel\Common\Name;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;
use DateTime;

class AttorneyTest extends AbstractDataModelTestCase
{
    /**
     * @var Name
     */
    private $name;

    public function setUp()
    {
        //  Set up the data entities to reuse
        $this->name = new Name([
            'title' => 'Mr',
            'first' => 'Chandler',
            'last'  => 'Bing',
        ]);
    }

    public function testGetsAndSets()
    {
        $model = new Attorney();

        $dob = new DateTime('1969-08-19');

        $model->setName($this->name)
              ->setDob($dob);

        $this->assertEquals($this->name, $model->getName());
        $this->assertEquals($dob, $model->getDob());
    }

    public function testPopulateAndToArray()
    {
        $dob = new DateTime('1969-08-19');

        $data = [
            'name'  => $this->name->toArray(),
            'dob'   => $this->dateTimeToString($dob),
        ];

        $model = new Attorney($data);

        $this->assertSame($data, $model->toArray());
    }
}
