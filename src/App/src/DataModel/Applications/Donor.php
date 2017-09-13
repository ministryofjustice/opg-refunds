<?php

namespace App\DataModel\Applications;

use App\DataModel\AbstractDataModel;
use App\DataModel\Common\Name;
use DateTime;

class Donor extends AbstractDataModel
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
     * @return Donor $this
     */
    public function setName(Name $name)
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
     * @return Donor $this
     */
    public function setDob(DateTime $dob)
    {
        $this->dob = $dob;
        return $this;
    }

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
