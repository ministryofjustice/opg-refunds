<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\Contact;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;

class ContactTest extends AbstractDataModelTestCase
{
    public function testGetsAndSets()
    {
        $model = new Contact();

        $model->setEmail('monica@friends.com')
              ->setPhone('07712 123456');

        $this->assertEquals('monica@friends.com', $model->getEmail());
        $this->assertEquals('07712 123456', $model->getPhone());
    }

    public function testPopulateAndToArray()
    {
        $data = [
            'email'  => 'monica@friends.com',
            'phone' => '07712 123456',
        ];

        $model = new Contact($data);

        $this->assertSame($data, $model->toArray());
    }
}
