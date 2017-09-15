<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\Donor;
use Opg\Refunds\Caseworker\DataModel\Common\Name;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;
use DateTime;

class DonorTest extends AbstractDataModelTestCase
{
    /**
     * @var Name
     */
    private $name;

    /**
     * @var Name
     */
    private $poaName;

    public function setUp()
    {
        //  Set up the data entities to reuse
        $this->name = new Name([
            'title' => 'Miss',
            'first' => 'Rachel',
            'last'  => 'Green',
        ]);

        $this->poaName = new Name([
            'title' => 'Mrs',
            'first' => 'Someone',
            'last'  => 'Else',
        ]);
    }

    public function testGetsAndSets()
    {
        $model = new Donor();

        $dob = new DateTime('1969-02-11');

        $model->setName($this->name)
              ->setPoaName($this->poaName)
              ->setDob($dob);

        $this->assertEquals($this->name, $model->getName());
        $this->assertEquals($this->poaName, $model->getPoaName());
        $this->assertEquals($dob, $model->getDob());
    }

    public function testPopulateAndToArray()
    {
        $dob = new DateTime('1969-02-11');

        $data = [
            'name'     => $this->name->toArray(),
            'poa-name' => $this->poaName->toArray(),
            'dob'      => $this->dateTimeToString($dob),
        ];

        $model = new Donor($data);

        $this->assertSame($data, $model->toArray());
    }
}
