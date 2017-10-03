<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\Attorney;
use Opg\Refunds\Caseworker\DataModel\Applications\Current;
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

        $current = new Current();

        $dob = new DateTime('1969-08-19');

        $current->setName($this->name)
                ->setDob($dob);

        $model->setCurrent($current);

        $this->assertEquals($this->name, $model->getCurrent()->getName());
        $this->assertEquals($dob, $model->getCurrent()->getDob());
    }

    public function testPopulateAndGetArrayCopy()
    {
        $dob = new DateTime('1969-08-19');

        $data = [
            'current' => [
                'name'  => $this->name->getArrayCopy(),
                'dob'   => $this->dateTimeToString($dob),
            ]
        ];

        $model = new Attorney($data);

        $this->assertSame($data, $model->getArrayCopy());
    }
}
