<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use Doctrine\Common\Collections\Collection;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="poa")
 **/
class Poa extends AbstractEntity
{
    /**
     * Class of the datamodel that this entity can be converted to
     *
     * @var string
     */
    protected $dataModelClass = PoaModel::class;

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
     * @ORM\Column(name="received_date", type="date")
     */
    protected $receivedDate;

    /**
     * @var string
     * @ORM\Column(name="original_payment_amount", type="string")
     */
    protected $originalPaymentAmount;

    /**
     * @var Claim
     * @ORM\ManyToOne(targetEntity="Claim")
     * @ORM\JoinColumn(name="claim_id", referencedColumnName="id")
     */
    protected $claim;

    /**
     * @var Collection|Verification[]
     * @ORM\OneToMany(targetEntity="Verification", mappedBy="poa", cascade={"persist", "remove"})
     */
    protected $verifications;

    public function __construct(string $system, string $caseNumber, DateTime $receivedDate, string $originalPaymentAmount, Claim $claim)
    {
        $this->system = $system;
        $this->caseNumber = $caseNumber;
        $this->receivedDate = $receivedDate;
        $this->originalPaymentAmount = $originalPaymentAmount;
        $this->claim = $claim;
    }

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
    public function getReceivedDate(): DateTime
    {
        return $this->receivedDate;
    }

    /**
     * @param DateTime $receivedDate
     */
    public function setReceivedDate(DateTime $receivedDate)
    {
        $this->receivedDate = $receivedDate;
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

    /**
     * @return Collection|Verification[]
     */
    public function getVerifications()
    {
        return $this->verifications;
    }

    /**
     * @param Collection|Verification[] $verifications
     */
    public function setVerifications($verifications)
    {
        $this->verifications = $verifications;
    }
}
