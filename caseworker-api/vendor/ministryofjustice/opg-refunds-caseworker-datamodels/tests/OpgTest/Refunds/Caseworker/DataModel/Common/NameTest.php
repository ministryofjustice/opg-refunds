<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Common;

use Opg\Refunds\Caseworker\DataModel\Common\Name;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;

class NameTest extends AbstractDataModelTestCase
{
    public function testGetsAndSets()
    {
        $name = new Name();

        $name->setTitle('Mr')
             ->setFirst('Joey')
             ->setLast('Tribbiani');

        $this->assertEquals('Mr', $name->getTitle());
        $this->assertEquals('Joey', $name->getFirst());
        $this->assertEquals('Tribbiani', $name->getLast());
    }

    public function testPopulateAndGetArrayCopy()
    {
        $data = [
            'title' => 'Mr',
            'first' => 'Joey',
            'last'  => 'Tribbiani',
        ];

        $name = new Name($data);

        $this->assertSame($data, $name->getArrayCopy());
    }
}
