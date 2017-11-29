<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Applications\Application as ApplicationModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;

/**
 * @ORM\Entity @ORM\Table(name="claim", indexes={@ORM\Index(name="idx_status", columns={"status"})})
 **/
class Claim extends AbstractEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="bigint")
     */
    protected $id;

    /**
     * @var DateTime
     * @ORM\Column(name="created_datetime", type="datetimetz")
     */
    protected $createdDateTime;

    /**
     * @var DateTime
     * @ORM\Column(name="updated_datetime", type="datetimetz", nullable=true)
     */
    protected $updatedDateTime;

    /**
     * @var DateTime
     * @ORM\Column(name="received_datetime", type="datetimetz")
     */
    protected $receivedDateTime;

    /**
     * @var array
     * @ORM\Column(name="json_data", type="json_array")
     */
    protected $jsonData;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="assignedClaims")
     * @ORM\JoinColumn(name="assigned_to_id", referencedColumnName="id", nullable=true)
     */
    protected $assignedTo;

    /**
     * @var DateTime
     * @ORM\Column(name="assigned_datetime", type="datetimetz", nullable=true)
     */
    protected $assignedDateTime;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="finished_by_id", referencedColumnName="id", nullable=true)
     */
    protected $finishedBy;

    /**
     * @var DateTime
     * @ORM\Column(name="finished_datetime", type="datetimetz", nullable=true)
     */
    protected $finishedDateTime;

    /**
     * @var string
     * @ORM\Column(name="donor_name", type="string")
     */
    protected $donorName;

    /**
     * @var string
     * @ORM\Column(name="account_hash", type="string", nullable=true)
     */
    protected $accountHash;

    /**
     * @var Collection|Poa[]
     * @ORM\OneToMany(targetEntity="Poa", mappedBy="claim", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $poas;

    /**
     * @var bool
     * @ORM\Column(name="no_sirius_poas", type="boolean")
     */
    protected $noSiriusPoas;

    /**
     * @var bool
     * @ORM\Column(name="no_meris_poas", type="boolean")
     */
    protected $noMerisPoas;

    /**
     * @var string
     * @ORM\Column(name="rejection_reason", type="string", nullable=true)
     */
    protected $rejectionReason;

    /**
     * @var string
     * @ORM\Column(name="rejection_reason_description", type="string", nullable=true)
     */
    protected $rejectionReasonDescription;

    /**
     * @var bool
     * @ORM\Column(name="outcome_email_sent", type="boolean", options={"default" : false})
     */
    protected $outcomeEmailSent;

    /**
     * @var bool
     * @ORM\Column(name="outcome_text_sent", type="boolean", options={"default" : false})
     */
    protected $outcomeTextSent;

    /**
     * @var Payment
     * @ORM\OneToOne(targetEntity="Payment", inversedBy="claim", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id")
     */
    protected $payment;

    /**
     * @var Collection|Note[]
     * @ORM\OneToMany(targetEntity="Note", mappedBy="claim", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdDateTime" = "DESC"})
     */
    protected $notes;

    /**
     * @var Collection|Claim[]
     * @ORM\ManyToMany(targetEntity="Claim", inversedBy="duplicateClaims")
     * @ORM\JoinTable(name="duplicate_claims",
     *      joinColumns={@ORM\JoinColumn(name="claim_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="duplicate_claim_id", referencedColumnName="id")})
     */
    private $duplicateOf;

    /**
     * @var Collection|Claim[]
     * @ORM\ManyToMany(targetEntity="Claim", mappedBy="duplicateOf")
     */
    private $duplicateClaims;

    public function __construct(int $id, DateTime $receivedDateTime, array $jsonData, string $donorName, $accountHash)
    {
        $this->id = $id;
        $this->receivedDateTime = $receivedDateTime;
        $this->jsonData = $jsonData;
        $this->donorName = $donorName;
        $this->accountHash = $accountHash;

        $this->createdDateTime = new DateTime();
        $this->status = ClaimModel::STATUS_PENDING;
        $this->noSiriusPoas = false;
        $this->noMerisPoas = false;
        $this->outcomeEmailSent = false;
        $this->outcomeTextSent = false;
        $this->duplicateClaims = new ArrayCollection();
        $this->duplicateOf = new ArrayCollection();
    }

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
     * @return DateTime
     */
    public function getUpdatedDateTime()
    {
        return $this->updatedDateTime;
    }

    /**
     * @param DateTime $updatedDateTime
     */
    public function setUpdatedDateTime(DateTime $updatedDateTime)
    {
        $this->updatedDateTime = $updatedDateTime;
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
     * @return array
     */
    public function getJsonData()
    {
        return $this->jsonData;
    }

    /**
     * @param array $jsonData
     */
    public function setJsonData(array $jsonData)
    {
        $this->jsonData = $jsonData;
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
     * @return User
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    /**
     * @param User $assignedTo
     */
    public function setAssignedTo($assignedTo)
    {
        $this->assignedTo = $assignedTo;
    }

    /**
     * @return DateTime
     */
    public function getAssignedDateTime()
    {
        return $this->assignedDateTime;
    }

    /**
     * @param DateTime $assignedDateTime
     */
    public function setAssignedDateTime($assignedDateTime)
    {
        $this->assignedDateTime = $assignedDateTime;
    }

    /**
     * @return User
     */
    public function getFinishedBy()
    {
        return $this->finishedBy;
    }

    /**
     * @param User $finishedBy
     */
    public function setFinishedBy($finishedBy)
    {
        $this->finishedBy = $finishedBy;
    }

    /**
     * @return DateTime
     */
    public function getFinishedDateTime()
    {
        return $this->finishedDateTime;
    }

    /**
     * @param DateTime $finishedDateTime
     */
    public function setFinishedDateTime($finishedDateTime)
    {
        $this->finishedDateTime = $finishedDateTime;
    }

    /**
     * @return string
     */
    public function getDonorName(): string
    {
        return $this->donorName;
    }

    /**
     * @param string $donorName
     */
    public function setDonorName(string $donorName)
    {
        $this->donorName = $donorName;
    }

    /**
     * @return string
     */
    public function getAccountHash()
    {
        return $this->accountHash;
    }

    /**
     * @param string $accountHash
     */
    public function setAccountHash(string $accountHash)
    {
        $this->accountHash = $accountHash;
    }

    /**
     * @return Collection|Poa[]
     */
    public function getPoas()
    {
        return $this->poas;
    }

    /**
     * @param Collection|Poa[] $poas
     */
    public function setPoas(array $poas)
    {
        $this->poas = $poas;
    }

    /**
     * @return bool
     */
    public function isNoSiriusPoas(): bool
    {
        return $this->noSiriusPoas;
    }

    /**
     * @param bool $noSiriusPoas
     */
    public function setNoSiriusPoas(bool $noSiriusPoas)
    {
        $this->noSiriusPoas = $noSiriusPoas;
    }

    /**
     * @return bool
     */
    public function isNoMerisPoas(): bool
    {
        return $this->noMerisPoas;
    }

    /**
     * @param bool $noMerisPoas
     */
    public function setNoMerisPoas(bool $noMerisPoas)
    {
        $this->noMerisPoas = $noMerisPoas;
    }

    /**
     * @return string
     */
    public function getRejectionReason()
    {
        return $this->rejectionReason;
    }

    /**
     * @param string $rejectionReason
     */
    public function setRejectionReason($rejectionReason)
    {
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * @return string
     */
    public function getRejectionReasonDescription()
    {
        return $this->rejectionReasonDescription;
    }

    /**
     * @param string $rejectionReasonDescription
     */
    public function setRejectionReasonDescription($rejectionReasonDescription)
    {
        $this->rejectionReasonDescription = $rejectionReasonDescription;
    }

    /**
     * @return bool
     */
    public function isOutcomeEmailSent(): bool
    {
        return $this->outcomeEmailSent;
    }

    /**
     * @param bool $outcomeEmailSent
     */
    public function setOutcomeEmailSent(bool $outcomeEmailSent)
    {
        $this->outcomeEmailSent = $outcomeEmailSent;
    }

    /**
     * @return bool
     */
    public function isOutcomeTextSent(): bool
    {
        return $this->outcomeTextSent;
    }

    /**
     * @param bool $outcomeTextSent
     */
    public function setOutcomeTextSent(bool $outcomeTextSent)
    {
        $this->outcomeTextSent = $outcomeTextSent;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return Collection|Note[]
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param Collection|Note[] $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return Claim[]|Collection
     */
    public function getDuplicateClaims()
    {
        return $this->duplicateClaims;
    }

    /**
     * @param Claim[]|Collection $duplicateClaims
     */
    public function setDuplicateClaims($duplicateClaims)
    {
        $this->duplicateClaims = $duplicateClaims;
    }

    /**
     * @return Claim[]|Collection
     */
    public function getDuplicateOf()
    {
        return $this->duplicateOf;
    }

    /**
     * @param Claim[]|Collection $duplicateOf
     */
    public function setDuplicateOf($duplicateOf)
    {
        $this->duplicateOf = $duplicateOf;
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
    public function getAsDataModel(array $modelToEntityMappings = [], string $dataModelClass = ClaimModel::class)
    {
        $modelToEntityMappings = array_merge($modelToEntityMappings, [
            'Application' => function () {
                return new ApplicationModel($this->getJsonData());
            },
            'AssignedToId' => function () {
                return ($this->getAssignedTo() instanceof User ? $this->getAssignedTo()->getId() : null);
            },
            'AssignedToName' => function () {
                return ($this->getAssignedTo() instanceof User ? $this->getAssignedTo()->getName() : null);
            },
            'AssignedToStatus' => function () {
                return ($this->getAssignedTo() instanceof User ? $this->getAssignedTo()->getStatus() : null);
            },
            'FinishedById' => function () {
                return ($this->getFinishedBy() instanceof User ? $this->getFinishedBy()->getId() : null);
            },
            'FinishedByName' => function () {
                return ($this->getFinishedBy() instanceof User ? $this->getFinishedBy()->getName() : null);
            },
            'FinishedByStatus' => function () {
                return ($this->getFinishedBy() instanceof User ? $this->getFinishedBy()->getStatus() : null);
            },
            'AssistedDigital' => function () {
                return isset($this->getJsonData()['ad']);
            },
            'DuplicateOfIds' => function () {
                $duplicateOfIds = [];
                foreach ($this->getDuplicateOf() as $duplicateClaim) {
                    $duplicateOfIds[$duplicateClaim->getId()] = IdentFormatter::format($duplicateClaim->getId());
                }
                return $duplicateOfIds;
            },
            'DuplicateClaimIds' => function () {
                $duplicateClaimIds = [];
                foreach ($this->getDuplicateClaims() as $duplicateClaim) {
                    $duplicateClaimIds[$duplicateClaim->getId()] = IdentFormatter::format($duplicateClaim->getId());
                }
                return $duplicateClaimIds;
            },
        ]);

        return parent::getAsDataModel($modelToEntityMappings, $dataModelClass);
    }
}
