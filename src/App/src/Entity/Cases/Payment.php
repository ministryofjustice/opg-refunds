<?php

namespace App\Entity\Cases;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="payment")
 **/
class Payment
{
    /**
     * @var int
     * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="refund_case_id", type="integer")
     */
    private $refundCaseId;

    /**
     * @var float
     * @ORM\Column(type="decimal")
     */
    private $amount;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $method;

    /**
     * @var DateTime
     * @ORM\Column(name="added_datetime", type="datetime")
     */
    private $addedDateTime;

    /**
     * @var DateTime
     * @ORM\Column(name="processed_datetime", type="datetime")
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