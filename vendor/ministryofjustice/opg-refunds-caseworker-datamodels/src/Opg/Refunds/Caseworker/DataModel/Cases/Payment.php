<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use DateTime;

/**
 * Class Payment
 * @package Opg\Refunds\Caseworker\DataModel\Cases
 */
class Payment extends AbstractDataModel
{
    /**
     * @var float
     */
    protected $amount;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var DateTime
     */
    protected $addedDateTime;

    /**
     * @var DateTime
     */
    protected $processedDateTime;

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setAmount(float $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getAddedDateTime(): DateTime
    {
        return $this->addedDateTime;
    }

    /**
     * @param DateTime $addedDateTime
     * @return $this
     */
    public function setAddedDateTime(DateTime $addedDateTime)
    {
        $this->addedDateTime = $addedDateTime;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getProcessedDateTime()
    {
        return $this->processedDateTime;
    }

    /**
     * @param DateTime $processedDateTime
     * @return $this
     */
    public function setProcessedDateTime(DateTime $processedDateTime)
    {
        $this->processedDateTime = $processedDateTime;

        return $this;
    }

    /**
     * Map properties to correct types
     *
     * @param string $property
     * @param mixed $value
     * @return DateTime|mixed
     */
    protected function map($property, $value)
    {
        switch ($property) {
            case 'addedDateTime':
            case 'processedDateTime':
                return (($value instanceof DateTime || is_null($value)) ? $value : new DateTime($value));
            default:
                return parent::map($property, $value);
        }
    }
}
