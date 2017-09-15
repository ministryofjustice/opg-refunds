<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\Account;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;

class AccountTest extends AbstractDataModelTestCase
{
    public function testGetsAndSets()
    {
        $model = new Account();

        $model->setName('Phoebe Buffay')
              ->setAccountNumber('12345678')
              ->setSortCode('123456');

        $this->assertEquals('Phoebe Buffay', $model->getName());
        $this->assertEquals('12345678', $model->getAccountNumber());
        $this->assertEquals('123456', $model->getSortCode());
    }

    public function testPopulateAndToArray()
    {
        $data = [
            'name'           => 'Phoebe Buffay',
            'account-number' => '12345678',
            'sort-code'      => '123456',
        ];

        $model = new Account($data);

        $this->assertSame($data, $model->toArray());
    }
}
