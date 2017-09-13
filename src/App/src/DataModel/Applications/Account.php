<?php

namespace App\DataModel\Applications;

use App\DataModel\AbstractDataModel;

class Account extends AbstractDataModel
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $accountNumber;

    /**
     * @var string
     */
    protected $sortCode;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     */
    public function setAccountNumber(string $accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return string
     */
    public function getSortCode()
    {
        return $this->sortCode;
    }

    /**
     * @param string $sortCode
     */
    public function setSortCode(string $sortCode)
    {
        $this->sortCode = $sortCode;
    }
}