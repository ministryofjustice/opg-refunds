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
     * @var Name
     */
    protected $name;

    /**
     * @var Name
     */
    protected $poaName;

    /**
     * @var DateTime
     */
    protected $dob;

    /**
     * @return Name
     */
    public function getName(): Name
    {
        return $this->name;
    }

    /**
     * @param Name $name
     * @return $this
     */
    public function setName(Name $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Name
     */
    public function getPoaName()
    {
        return $this->poaName;
    }

    /**
     * @param Name $poaName
     * @return $this
     */
    public function setPoaName(Name $poaName)
    {
        $this->poaName = $poaName;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDob(): DateTime
    {
        return $this->dob;
    }

    /**
     * @param DateTime $dob
     * @return $this
     */
    public function setDob(DateTime $dob)
    {
        $this->dob = $dob;

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
            case 'name':
            case 'poaName':
                return (($value instanceof Name || is_null($value)) ? $value : new Name($value));
            case 'dob':
                return (($value instanceof DateTime || is_null($value)) ? $value : new DateTime($value));
            default:
                return parent::map($property, $value);
        }
    }
}