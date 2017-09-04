<?php

namespace App\Entity;

use DateTime;

class Log
{
    /**
     * @var int
     */
    private $refundCaseId;

    /**
     * @var int
     */
    private $caseworkerId;

    /**
     * @var int
     */
    private $poaId;

    /**
     * @var DateTime
     */
    private $createdDateTime;

    /**
     * @var string
     */
    private $message;

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
     * @return int
     */
    public function getCaseworkerId(): int
    {
        return $this->caseworkerId;
    }

    /**
     * @param int $caseworkerId
     */
    public function setCaseworkerId(int $caseworkerId)
    {
        $this->caseworkerId = $caseworkerId;
    }

    /**
     * @return int
     */
    public function getPoaId(): int
    {
        return $this->poaId;
    }

    /**
     * @param int $poaId
     */
    public function setPoaId(int $poaId)
    {
        $this->poaId = $poaId;
    }

    /**
     * @return DateTime
     */
    public function getCreatedDateTime(): DateTime
    {
        return $this->createdDateTime;
    }

    /**
     * @param DateTime $createdDateTime
     */
    public function setCreatedDateTime(DateTime $createdDateTime)
    {
        $this->createdDateTime = $createdDateTime;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }
}