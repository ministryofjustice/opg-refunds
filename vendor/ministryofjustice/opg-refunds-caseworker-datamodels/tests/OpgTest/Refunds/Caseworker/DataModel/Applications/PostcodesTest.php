<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\Postcodes;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;

class PostcodesTest extends AbstractDataModelTestCase
{
    public function testGetsAndSets()
    {
        $model = new Postcodes();

        $model->setDonorPostcode('AB1 2CD')
              ->setAttorneyPostcode('WX8 YZ9');

        $this->assertEquals('AB1 2CD', $model->getDonorPostcode());
        $this->assertEquals('WX8 YZ9', $model->getAttorneyPostcode());
    }

    public function testPopulateAndToArray()
    {
        $data = [
            'donor-postcode'    => 'AB1 2CD',
            'attorney-postcode' => 'WX8 YZ9',
        ];

        $model = new Postcodes($data);

        $this->assertSame($data, $model->toArray());
    }
}
