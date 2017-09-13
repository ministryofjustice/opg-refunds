<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="payment")
 **/
class Payment extends AbstractEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var float
     * @ORM\Column(type="decimal")
     */
    protected $amount;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $method;

    /**
     * @var DateTime
     * @ORM\Column(name="added_datetime", type="datetimetz")
     */
    protected $addedDateTime;

    /**
     * @var DateTime
     * @ORM\Column(name="processed_datetime", type="datetimetz")
     */
    protected $processedDateTime;

    /**
     * @var RefundCase
     * @ORM\OneToOne(targetEntity="RefundCase", inversedBy="payment")
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