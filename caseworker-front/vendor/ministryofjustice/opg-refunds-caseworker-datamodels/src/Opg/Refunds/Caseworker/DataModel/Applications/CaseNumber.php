<?php

namespace Opg\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

/**
 * Class CaseNumber
 * @package Opg\Refunds\Caseworker\DataModel\Applications
 */
class CaseNumber extends AbstractDataModel
{
    /**
     * @var string
     */
    protected $poaCaseNumber;

    /**
     * @return string
     */
    public function getPoaCaseNumber()
    {
        return $this->poaCaseNumber;
    }

    /**
     * @param string $poaCaseNumber
     * @return $this
     */
    public function setPoaCaseNumber(string $poaCaseNumber): CaseNumber
    {
        $this->poaCaseNumber = $poaCaseNumber;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPoaCaseNumber()
    {
        return !empty($this->poaCaseNumber);
    }
}