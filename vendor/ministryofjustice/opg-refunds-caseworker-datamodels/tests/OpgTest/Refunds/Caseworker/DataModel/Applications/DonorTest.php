<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\CurrentWithAddress;
use Opg\Refunds\Caseworker\DataModel\Applications\Donor;
use Opg\Refunds\Caseworker\DataModel\Applications\Poa;
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

        $current = new CurrentWithAddress();
        $poa = new Poa();

        $dob = new DateTime('1969-02-11');

        $current->setName($this->name)
                ->setDob($dob);

        $poa->setName($this->poaName);

        $model->setCurrent($current);
        $model->setPoa($poa);

        $this->assertEquals($this->name, $model->getCurrent()->getName());
        $this->assertEquals($this->poaName, $model->getPoa()->getName());
        $this->assertEquals($dob, $model->getCurrent()->getDob());
    }

    public function testPopulateAndGetArrayCopy()
    {
        $dob = new DateTime('1969-02-11');

        $data = [
            'current' => [
                'name'     => $this->name->getArrayCopy(),
                'dob'      => $this->dateTimeToString($dob),
            ],
            'poa'     => [
                'name' => $this->poaName->getArrayCopy(),
            ]
        ];

        $model = new Donor($data);

        $this->assertSame($data, $model->getArrayCopy());
    }
}
