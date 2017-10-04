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
     * @var string;
     */
    protected $phone;

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
}