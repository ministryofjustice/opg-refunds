<?php

namespace Opg\Refunds\Caseworker\DataModel\Common;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

class Address extends AbstractDataModel
{
    /**
     * @var string
     */
    protected $address1;

    /**
     * @var string
     */
    protected $address2;

    /**
     * @var string
     */
    protected $address3;

    /**
     * @var string
     */
    protected $addressPostcode;

    /**
     * @return string
     */
    public function getAddress1(): string
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     * @return $this
     */
    public function setAddress1(string $address1): Address
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     * @return $this
     */
    public function setAddress2($address2): Address
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @param string $address3
     * @return $this
     */
    public function setAddress3($address3): Address
    {
        $this->address3 = $address3;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressPostcode(): string
    {
        return $this->addressPostcode;
    }

    /**
     * @param string $addressPostcode
     * @return $this
     */
    public function setAddressPostcode(string $addressPostcode): Address
    {
        $this->addressPostcode = $addressPostcode;

        return $this;
    }
}