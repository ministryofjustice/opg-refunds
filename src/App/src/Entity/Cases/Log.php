<?php

namespace App\Entity\Cases;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="log")
 **/
class Log
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
     * @var int
     * @ORM\Column(name="caseworker_id", type="integer")
     */
    private $caseworkerId;

    /**
     * @var int
     * @ORM\Column(name="poa_id", type="integer")
     */
    private $poaId;

    /**
     * @var DateTime
     * @ORM\Column(name="created_datetime", type="datetime")
     */
    private $createdDateTime;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $message;

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