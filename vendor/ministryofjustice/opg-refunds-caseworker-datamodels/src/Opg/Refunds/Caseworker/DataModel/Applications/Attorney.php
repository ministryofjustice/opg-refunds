<?php

namespace Opg\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Common\Name;
use DateTime;

/**
 * Class Attorney
 * @package Opg\Refunds\Caseworker\DataModel\Applications
 */
class Attorney extends AbstractDataModel
{
    /**
     * @var Current
     */
    protected $current;

    /**
     * @var Poa
     */
    protected $poa;

    /**
     * @return Current
     */
    public function getCurrent(): Current
    {
        return $this->current;
    }

    /**
     * @param Current $current
     * @return $this
     */
    public function setCurrent(Current $current): Attorney
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
    public function setPoa(Poa $poa): Attorney
    {
        $this->poa = $poa;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPoaName(): bool
    {
        return $this->poa !== null;
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
                return (($value instanceof Current || is_null($value)) ? $value : new Current($value));
            case 'poa':
                return (($value instanceof Poa || is_null($value)) ? $value : new Poa($value));
            default:
                return parent::map($property, $value);
        }
    }
}