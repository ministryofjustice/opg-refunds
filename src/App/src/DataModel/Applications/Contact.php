<?php

namespace App\DataModel\Applications;

use App\DataModel\AbstractDataModel;

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
     * @return Contact $this
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
     * @return Contact $this
     */
    public function setMobile(string $mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }
}
