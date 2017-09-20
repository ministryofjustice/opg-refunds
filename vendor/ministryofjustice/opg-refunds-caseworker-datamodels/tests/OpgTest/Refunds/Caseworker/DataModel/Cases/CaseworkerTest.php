<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Cases\Caseworker;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;

class CaseworkerTest extends AbstractDataModelTestCase
{
    public function testGetsAndSets()
    {
        $model = new Caseworker();

        $model->setId(5)
              ->setName('Mr Case Worker')
              ->setEmail('case.worker@digital.justice.gov.uk')
              ->setStatus(1)
              ->setRoles(Caseworker::ROLE_CASEWORKER)
              ->setToken('abcdefghijklmnopqrstuvwxyz');

        $this->assertEquals(5, $model->getId());
        $this->assertEquals('Mr Case Worker', $model->getName());
        $this->assertEquals('case.worker@digital.justice.gov.uk', $model->getEmail());
        $this->assertEquals(1, $model->getStatus());
        $this->assertEquals(Caseworker::ROLE_CASEWORKER, $model->getRoles());
        $this->assertEquals('abcdefghijklmnopqrstuvwxyz', $model->getToken());
    }

    public function testPopulateAndToArray()
    {
        $data = [
            'id' => 5,
            'name' => 'Mr Case Worker',
            'email' => 'case.worker@digital.justice.gov.uk',
            'status' => 1,
            'roles' => Caseworker::ROLE_CASEWORKER,
            'token' => 'abcdefghijklmnopqrstuvwxyz',
        ];

        $model = new Caseworker($data);

        $this->assertSame($data, $model->toArray());
    }
}
