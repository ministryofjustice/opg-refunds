<?php

namespace App\DataModel\Applications;

use App\DataModel\AbstractDataModel;

class Account extends AbstractDataModel
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }
}