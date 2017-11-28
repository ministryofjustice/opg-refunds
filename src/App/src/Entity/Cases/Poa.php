<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use App\Service\RefundCalculator as RefundCalculatorService;
use Doctrine\Common\Collections\Collection;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="poa", uniqueConstraints={@ORM\UniqueConstraint(name="case_number_idx", columns={"system", "case_number", "case_number_available"})})
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
     * @var bool
     * @ORM\Column(name="case_number_available", type="boolean", options={"default" : false})
     */
    protected $caseNumberAvailable;

    /**
     * @var DateTime
     * @ORM\Column(name="received_date", type="date")
     */
    protected $receivedDate;

    /**
     * @var string
     * @ORM\Column(name="original_payment_amount", type="string", nullable=true)
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

    public function __construct(string $system, string $caseNumber, DateTime $receivedDate, $originalPaymentAmount, Claim $claim)
    {
        $this->system = $system;
        $this->caseNumber = $caseNumber;
        $this->caseNumberAvailable = false;
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
     * @return bool
     */
    public function isCaseNumberAvailable(): bool
    {
        return $this->caseNumberAvailable;
    }

    /**
     * @param bool $caseNumberAvailable
     */
    public function setCaseNumberAvailable(bool $caseNumberAvailable)
    {
        $this->caseNumberAvailable = $caseNumberAvailable;
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
    public function getOriginalPaymentAmount()
    {
        return $this->originalPaymentAmount;
    }

    /**
     * @param string $originalPaymentAmount
     */
    public function setOriginalPaymentAmount($originalPaymentAmount)
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

    /**
     * Returns the entity as a datamodel structure
     *
     * In the $modelToEntityMappings array key values reflect the set method to be used in the datamodel
     * for example a mapping of 'Something' => 'AnotherThing' will result in $model->setSomething($entity->getAnotherThing());
     * The value in the mapping array can also be a callback function
     *
     * @param array $modelToEntityMappings
     * @param string|null $dataModelClass
     * @return AbstractDataModel
     */
    public function getAsDataModel(array $modelToEntityMappings = [], string $dataModelClass = PoaModel::class)
    {
        $modelToEntityMappings = array_merge($modelToEntityMappings, [
            'RefundAmount' => function () {
                return RefundCalculatorService::getRefundAmount(
                    $this->getOriginalPaymentAmount(),
                    $this->getReceivedDate()
                );
            },
            'RefundInterestAmount' => function () {
                return RefundCalculatorService::getRefundInterestAmount(
                    $this->getOriginalPaymentAmount(),
                    $this->getReceivedDate(),
                    time()
                );
            },
        ]);

        return parent::getAsDataModel($modelToEntityMappings, $dataModelClass);
    }
}
