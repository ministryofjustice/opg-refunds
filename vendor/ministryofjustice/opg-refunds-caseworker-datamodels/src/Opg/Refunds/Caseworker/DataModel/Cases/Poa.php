<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use DateTime;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

/**
 * Class Poa
 * @package Opg\Refunds\Caseworker\DataModel\Cases
 */
class Poa extends AbstractDataModel
{
    const SYSTEM_SIRIUS = 'sirius';
    const SYSTEM_MERIS = 'meris';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $system;

    /**
     * @var string
     */
    protected $caseNumber;

    /**
     * @var DateTime
     */
    protected $receivedDate;

    /**
     * @var string
     */
    protected $originalPaymentAmount;

    /**
     * @var Verification[]
     */
    protected $verifications;

    /**
     * @var float
     */
    protected $refundAmount;

    /**
     * @var float
     */
    protected $refundInterestAmount;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): Poa
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getSystem(): string
    {
        return $this->system;
    }

    /**
     * @param string $system
     * @return $this
     */
    public function setSystem(string $system): Poa
    {
        $this->system = $system;

        return $this;
    }

    /**
     * @return string
     */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
     * @param string $caseNumber
     * @return $this
     */
    public function setCaseNumber(string $caseNumber): Poa
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getReceivedDate(): DateTime
    {
        return $this->receivedDate;
    }

    /**
     * @param DateTime $receivedDate
     * @return $this
     */
    public function setReceivedDate(DateTime $receivedDate): Poa
    {
        $this->receivedDate = $receivedDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalPaymentAmount()
    {
        return $this->originalPaymentAmount;
    }

    /**
     * @param string $originalPaymentAmount
     * @return $this
     */
    public function setOriginalPaymentAmount(string $originalPaymentAmount): Poa
    {
        $this->originalPaymentAmount = $originalPaymentAmount;

        return $this;
    }

    /**
     * @return Verification[]
     */
    public function getVerifications()
    {
        return $this->verifications;
    }

    /**
     * @param Verification[] $verifications
     * @return $this
     */
    public function setVerifications(array $verifications): Poa
    {
        $this->verifications = $verifications;

        return $this;
    }

    /**
     * @return float
     */
    public function getRefundAmount(): float
    {
        return $this->refundAmount;
    }

    /**
     * @param float $refundAmount
     * @return $this
     */
    public function setRefundAmount(float $refundAmount): Poa
    {
        $this->refundAmount = $refundAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getRefundInterestAmount(): float
    {
        return $this->refundInterestAmount;
    }

    /**
     * @param float $refundInterestAmount
     * @return $this
     */
    public function setRefundInterestAmount(float $refundInterestAmount): Poa
    {
        $this->refundInterestAmount = $refundInterestAmount;

        return $this;
    }

    /**
     * @return bool
     */
    public function isComplete(): bool
    {
        return !empty($this->caseNumber) && !empty($this->receivedDate) && !empty($this->originalPaymentAmount);
    }

    /**
     * Map properties to correct types
     *
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    protected function map($property, $value)
    {
        switch ($property) {
            case 'receivedDate':
                return (($value instanceof DateTime || is_null($value)) ? $value : new DateTime($value));
            case 'verifications':
                return array_map(function ($value) {
                    return ($value instanceof Verification ? $value : new Verification($value));
                }, $value);
            default:
                return parent::map($property, $value);
        }
    }
}
