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
     * @var string
     * @ORM\Column(type="string")
     */
    protected $system;

    /**
     * @var string
     * @ORM\Column(name="case_number", type="string")
     */
    protected $caseNumber;

    /**
     * @var DateTime
     * @ORM\Column(name="received_datetime", type="date")
     */
    protected $receivedDateTime;

    /**
     * @var string
     * @ORM\Column(name="original_payment_amount", type="string")
     */
    protected $originalPaymentAmount;

    /**
     * @var Claim
     * @ORM\ManyToOne(targetEntity="Claim", inversedBy="poas")
     * @ORM\JoinColumn(name="claim_id", referencedColumnName="id")
     */
    protected $claim;

    /**
     * @var Verification[]
     * @ORM\OneToMany(targetEntity="Verification", mappedBy="poa", cascade={"persist", "remove"})
     */
    protected $verifications;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSystem(): string
    {
        return $this->system;
    }

    /**
     * @param string $system
     */
    public function setSystem(string $system)
    {
        $this->system = $system;
    }

    /**
     * @return string
     */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
     * @param string $caseNumber
     */
    public function setCaseNumber(string $caseNumber)
    {
        $this->caseNumber = $caseNumber;
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
     * @return string
     */
    public function getOriginalPaymentAmount(): string
    {
        return $this->originalPaymentAmount;
    }

    /**
     * @param string $originalPaymentAmount
     */
    public function setOriginalPaymentAmount(string $originalPaymentAmount)
    {
        $this->originalPaymentAmount = $originalPaymentAmount;
    }

    /**
     * @return Claim
     */
    public function getClaim(): Claim
    {
        return $this->claim;
    }

    /**
     * @param Claim $claim
     */
    public function setClaim(Claim $claim)
    {
        $this->claim = $claim;
    }
}
