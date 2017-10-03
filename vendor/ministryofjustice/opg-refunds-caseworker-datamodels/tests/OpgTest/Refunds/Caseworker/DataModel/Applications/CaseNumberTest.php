<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\CaseNumber;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;

class CaseNumberTest extends AbstractDataModelTestCase
{
    public function testGetsAndSets()
    {
        $model = new CaseNumber();

        $model->setPoaCaseNumber('123456789');

        $this->assertEquals('123456789', $model->getPoaCaseNumber());
    }

    public function testPopulateAndGetArrayCopy()
    {
        $data = [
            'poa-case-number' => '123456789'
        ];

        $model = new CaseNumber($data);

        $this->assertSame($data, $model->getArrayCopy());
    }
}
