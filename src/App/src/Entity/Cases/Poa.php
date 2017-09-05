<?php

namespace App\Entity\Cases;

use DateTime;

class Poa
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
     * @var DateTime
     */
    private $receivedDateTime;

    /**
     * @var float
     */
    private $netAmountPaid;

    /**
     * @var int
     */
    private $status;

    /**
     * @var float
     */
    private $amountToRefund;

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
     * @return DateTime
     */
    public function getReceivedDateTime(): DateTime
    {
        return $this->receivedDateTime;
    }

    /**
     * @param DateTime $receivedDateTime
     */
    public function setReceivedDateTime(DateTime $receivedDateTime)
    {
        $this->receivedDateTime = $receivedDateTime;
    }

    /**
     * @return float
     */
    public function getNetAmountPaid(): float
    {
        return $this->netAmountPaid;
    }

    /**
     * @param float $netAmountPaid
     */
    public function setNetAmountPaid(float $netAmountPaid)
    {
        $this->netAmountPaid = $netAmountPaid;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    /**
     * @return float
     */
    public function getAmountToRefund(): float
    {
        return $this->amountToRefund;
    }

    /**
     * @param float $amountToRefund
     */
    public function setAmountToRefund(float $amountToRefund)
    {
        $this->amountToRefund = $amountToRefund;
    }
}