<?php

namespace Opg\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

/**
 * Class Contact
 * @package Opg\Refunds\Caseworker\DataModel\Applications
 */
class Contact extends AbstractDataModel
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var bool
     */
    protected $receiveNotifications;

    public function __construct($data = null)
    {
        $this->receiveNotifications = true;

        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasEmail(): bool
    {
        return !empty($this->email);
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPhone(): bool
    {
        return !empty($this->phone);
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return $this
     */
    public function setAddress(string $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAddress(): bool
    {
        return !empty($this->address);
    }

    /**
     * @return bool
     */
    public function isReceiveNotifications(): bool
    {
        return $this->receiveNotifications;
    }

    /**
     * @param bool $receiveNotifications
     * @return $this
     */
    public function setReceiveNotifications(bool $receiveNotifications)
    {
        $this->receiveNotifications = $receiveNotifications;

        return $this;
    }
}