<?php

namespace Opg\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

/**
 * Class Postcodes
 * @package Opg\Refunds\Caseworker\DataModel\Applications
 */
class Postcodes extends AbstractDataModel
{
    /**
     * @var string
     */
    protected $donorPostcode;

    /**
     * @var string
     */
    protected $attorneyPostcode;

    /**
     * @return string
     */
    public function getDonorPostcode()
    {
        return $this->donorPostcode;
    }

    /**
     * @param string $donorPostcode
     * @return $this
     */
    public function setDonorPostcode(string $donorPostcode): Postcodes
    {
        $this->donorPostcode = $donorPostcode;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDonorPostcode(): bool
    {
        return !empty($this->donorPostcode);
    }

    /**
     * @return string
     */
    public function getAttorneyPostcode()
    {
        return $this->attorneyPostcode;
    }

    /**
     * @param string $attorneyPostcode
     * @return $this
     */
    public function setAttorneyPostcode(string $attorneyPostcode): Postcodes
    {
        $this->attorneyPostcode = $attorneyPostcode;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttorneyPostcode(): bool
    {
        return !empty($this->attorneyPostcode);
    }
}