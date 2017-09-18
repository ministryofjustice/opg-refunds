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
              ->setPasswordHash('p@55w0rdh@$hh3r3')
              ->setStatus(1)
              ->setRoles(Caseworker::ROLE_CASEWORKER)
              ->setToken('abcdefghijklmnopqrstuvwxyz')
              ->setTokenExpires(1504925010);

        $this->assertEquals(5, $model->getId());
        $this->assertEquals('Mr Case Worker', $model->getName());
        $this->assertEquals('case.worker@digital.justice.gov.uk', $model->getEmail());
        $this->assertEquals('p@55w0rdh@$hh3r3', $model->getPasswordHash());
        $this->assertEquals(1, $model->getStatus());
        $this->assertEquals(Caseworker::ROLE_CASEWORKER, $model->getRoles());
        $this->assertEquals('abcdefghijklmnopqrstuvwxyz', $model->getToken());
        $this->assertEquals(1504925010, $model->getTokenExpires());
    }

    public function testPopulateAndToArray()
    {
        $data = [
            'id' => 5,
            'name' => 'Mr Case Worker',
            'email' => 'case.worker@digital.justice.gov.uk',
            //  password-hash is not returned in toArray for security
            'status' => 1,
            'roles' => Caseworker::ROLE_CASEWORKER,
            'token' => 'abcdefghijklmnopqrstuvwxyz',
            'token-expires' => 1504925010,
        ];

        $model = new Caseworker($data);

        $this->assertSame($data, $model->toArray());
    }
}
