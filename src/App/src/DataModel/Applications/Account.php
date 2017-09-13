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
     * @return Account $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
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
     * @return Account $this
     */
    public function setAccountNumber(string $accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
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
     * @return Account $this
     */
    public function setSortCode(string $sortCode)
    {
        $this->sortCode = $sortCode;
        return $this;
    }
}
