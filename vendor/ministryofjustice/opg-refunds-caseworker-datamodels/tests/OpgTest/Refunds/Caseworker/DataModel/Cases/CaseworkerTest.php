<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Cases\User;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;

class CaseworkerTest extends AbstractDataModelTestCase
{
    public function testGetsAndSets()
    {
        $model = new User();

        $model->setId(5)
              ->setName('Mr Case Worker')
              ->setEmail('case.worker@digital.justice.gov.uk')
              ->setStatus(1)
              ->setRoles(User::ROLE_CASEWORKER)
              ->setToken('abcdefghijklmnopqrstuvwxyz');

        $this->assertEquals(5, $model->getId());
        $this->assertEquals('Mr Case Worker', $model->getName());
        $this->assertEquals('case.worker@digital.justice.gov.uk', $model->getEmail());
        $this->assertEquals(1, $model->getStatus());
        $this->assertEquals(User::ROLE_CASEWORKER, $model->getRoles());
        $this->assertEquals('abcdefghijklmnopqrstuvwxyz', $model->getToken());
    }

    public function testPopulateAndGetArrayCopy()
    {
        $data = [
            'id' => 5,
            'name' => 'Mr Case Worker',
            'email' => 'case.worker@digital.justice.gov.uk',
            'status' => 1,
            'roles' => User::ROLE_CASEWORKER,
            'token' => 'abcdefghijklmnopqrstuvwxyz',
        ];

        $model = new User($data);

        $this->assertSame($data, $model->getArrayCopy());
    }
}
