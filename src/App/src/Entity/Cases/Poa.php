<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="poa")
 **/
class Poa extends AbstractEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var DateTime
     * @ORM\Column(name="received_datetime", type="datetime")
     */
    protected $receivedDateTime;

    /**
     * @var float
     * @ORM\Column(name="net_amount_paid", type="decimal")
     */
    protected $netAmountPaid;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @var float
     * @ORM\Column(name="amount_to_refund", type="decimal")
     */
    protected $amountToRefund;

    /**
     * @var RefundCase
     * @ORM\ManyToOne(targetEntity="RefundCase", inversedBy="poas")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id")
     */
    protected $case;

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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
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