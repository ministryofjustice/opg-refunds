<?php

namespace App\DataModel\Cases;

use App\DataModel\AbstractDataModel;
use DateTime;

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
     * @return Payment $this
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
     * @return Payment $this
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
     * @return Payment $this
     */
    public function setAddedDateTime(DateTime $addedDateTime)
    {
        $this->addedDateTime = $addedDateTime;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getProcessedDateTime(): DateTime
    {
        return $this->processedDateTime;
    }

    /**
     * @param DateTime $processedDateTime
     * @return Payment $this
     */
    public function setProcessedDateTime(DateTime $processedDateTime)
    {
        $this->processedDateTime = $processedDateTime;
        return $this;
    }

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
