<?php

namespace Opg\Refunds\Caseworker\DataModel\Applications;

use DateTime;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Common\Address;
use Opg\Refunds\Caseworker\DataModel\Common\Name;

class Current extends AbstractDataModel
{
    /**
     * @var Name
     */
    protected $name;

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
    public function setName(Name $name): Current
    {
        $this->name = $name;

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
    public function setDob(DateTime $dob): Current
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
                return (($value instanceof Name || is_null($value)) ? $value : new Name($value));
            case 'dob':
                return (($value instanceof DateTime || is_null($value)) ? $value : new DateTime($value));
            default:
                return parent::map($property, $value);
        }
    }
}