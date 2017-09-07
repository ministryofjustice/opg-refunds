<?php

namespace App\Entity\Cases;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="poa")
 **/
class Poa
{
    /**
     * @var int
     * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var DateTime
     * @ORM\Column(name="received_datetime", type="datetime")
     */
    private $receivedDateTime;

    /**
     * @var float
     * @ORM\Column(name="net_amount_paid", type="decimal")
     */
    private $netAmountPaid;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @var float
     * @ORM\Column(name="amount_to_refund", type="decimal")
     */
    private $amountToRefund;

    /**
     * @var RefundCase
     * @ORM\ManyToOne(targetEntity="RefundCase", inversedBy="poas")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id")
     */
    private $case;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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

    /**
     * @return RefundCase
     */
    public function getCase(): RefundCase
    {
        return $this->case;
    }

    /**
     * @param RefundCase $case
     */
    public function setCase(RefundCase $case)
    {
        $this->case = $case;
    }
}