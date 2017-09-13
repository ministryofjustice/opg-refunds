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
     */
    public function setAmount(float $amount)
    {
        $this->amount = $amount;
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
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
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
     */
    public function setAddedDateTime(DateTime $addedDateTime)
    {
        $this->addedDateTime = $addedDateTime;
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
     */
    public function setProcessedDateTime(DateTime $processedDateTime)
    {
        $this->processedDateTime = $processedDateTime;
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