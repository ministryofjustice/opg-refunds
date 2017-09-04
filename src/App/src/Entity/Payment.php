<?php

namespace App\Entity;

use DateTime;

class Payment
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $refundCaseId;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $method;

    /**
     * @var DateTime
     */
    private $addedDateTime;

    /**
     * @var DateTime
     */
    private $processedDateTime;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getRefundCaseId(): int
    {
        return $this->refundCaseId;
    }

    /**
     * @param int $refundCaseId
     */
    public function setRefundCaseId(int $refundCaseId)
    {
        $this->refundCaseId = $refundCaseId;
    }

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
}