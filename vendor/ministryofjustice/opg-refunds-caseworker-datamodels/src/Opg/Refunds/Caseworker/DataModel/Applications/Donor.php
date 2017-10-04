<?php

namespace Opg\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Common\Name;
use DateTime;

/**
 * Class Donor
 * @package Opg\Refunds\Caseworker\DataModel\Applications
 */
class Donor extends AbstractDataModel
{
    /**
     * @var CurrentWithAddress
     */
    protected $current;

    /**
     * @var Poa
     */
    protected $poa;

    /**
     * @return CurrentWithAddress
     */
    public function getCurrent(): CurrentWithAddress
    {
        return $this->current;
    }

    /**
     * @param CurrentWithAddress $current
     * @return $this
     */
    public function setCurrent(CurrentWithAddress $current): Donor
    {
        $this->current = $current;

        return $this;
    }

    /**
     * @return Poa
     */
    public function getPoa()
    {
        return $this->poa;
    }

    /**
     * @param Poa $poa
     * @return $this
     */
    public function setPoa(Poa $poa): Donor
    {
        $this->poa = $poa;

        return $this;
    }

    /**
     * Map properties to correct types
     *
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    protected function map($property, $value)
    {
        switch ($property) {
            case 'current':
                return (($value instanceof CurrentWithAddress || is_null($value)) ? $value : new CurrentWithAddress($value));
            case 'poa':
                return (($value instanceof Poa || is_null($value)) ? $value : new Poa($value));
            default:
                return parent::map($property, $value);
        }
    }
}