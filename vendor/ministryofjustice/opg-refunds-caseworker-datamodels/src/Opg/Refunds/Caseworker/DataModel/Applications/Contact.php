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
    protected $mobile;

    /**
     * @return string
     */
    public function getEmail(): string
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
    public function getMobile(): string
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     * @return $this
     */
    public function setMobile(string $mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }
}