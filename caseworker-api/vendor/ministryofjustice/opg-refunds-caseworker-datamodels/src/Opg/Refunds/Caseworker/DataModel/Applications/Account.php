<?php

namespace Opg\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

/**
 * Class Account
 * @package Opg\Refunds\Caseworker\DataModel\Applications
 */
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
     * @var string
     */
    protected $institutionName;

    /**
     * @var bool
     */
    protected $buildingSociety;

    public function __construct($data = null)
    {
        $this->buildingSociety = false;

        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     * @return $this
     */
    public function setAccountNumber(string $accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortCode(): string
    {
        return $this->sortCode;
    }

    /**
     * @param string $sortCode
     * @return $this
     */
    public function setSortCode(string $sortCode)
    {
        $this->sortCode = $sortCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstitutionName()
    {
        return $this->institutionName;
    }

    /**
     * @param string $institutionName
     * @return $this
     */
    public function setInstitutionName($institutionName)
    {
        $this->institutionName = $institutionName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBuildingSociety(): bool
    {
        return $this->buildingSociety;
    }

    /**
     * @param bool $buildingSociety
     * @return $this
     */
    public function setBuildingSociety(bool $buildingSociety)
    {
        $this->buildingSociety = $buildingSociety;

        return $this;
    }
}
