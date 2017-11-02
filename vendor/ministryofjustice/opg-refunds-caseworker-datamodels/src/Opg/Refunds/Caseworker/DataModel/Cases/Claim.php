<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use DateTime;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;

/**
 * Class Claim
 * @package Opg\Refunds\Caseworker\DataModel\Cases
 */
class Claim extends AbstractDataModel
{
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ACCEPTED = 'accepted';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $referenceNumber;

    /**
     * @var DateTime
     */
    protected $createdDateTime;

    /**
     * @var DateTime
     */
    protected $updatedDateTime;

    /**
     * @var DateTime
     */
    protected $receivedDateTime;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var int
     */
    protected $assignedToId;

    /**
     * @var DateTime
     */
    protected $assignedDateTime;

    /**
     * @var int
     */
    protected $finishedById;

    /**
     * @var DateTime
     */
    protected $finishedDateTime;

    /**
     * @var string
     */
    protected $donorName;

    /**
     * @var string
     */
    protected $accountHash;

    /**
     * @var Poa[]
     */
    protected $poas;

    /**
     * @var bool
     */
    protected $noSiriusPoas;

    /**
     * @var bool
     */
    protected $noMerisPoas;

    /**
     * @var string
     */
    protected $rejectionReason;

    /**
     * @var string
     */
    protected $rejectionReasonDescription;

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @var Note[]
     */
    protected $notes;

    /**
     * @var int
     */
    protected $accountHashCount;

    /**
     * @var bool
     */
    protected $readOnly;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getReferenceNumber(): string
    {
        if (!is_null($this->id)) {
            return IdentFormatter::format($this->id);
        }

        return null;
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
     * @return $this
     */
    public function setCreatedDateTime(DateTime $createdDateTime)
    {
        $this->createdDateTime = $createdDateTime;

        return $this;
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
     * @return $this
     */
    public function setUpdatedDateTime(DateTime $updatedDateTime)
    {
        $this->updatedDateTime = $updatedDateTime;

        return $this;
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
     * @return $this
     */
    public function setReceivedDateTime(DateTime $receivedDateTime)
    {
        $this->receivedDateTime = $receivedDateTime;

        return $this;
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * @param Application $application
     * @return $this
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;

        return $this;
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
     * @return $this
     */
    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getAssignedToId()
    {
        return $this->assignedToId;
    }

    /**
     * @param int $assignedToId
     * @return $this
     */
    public function setAssignedToId(int $assignedToId)
    {
        $this->assignedToId = $assignedToId;

        return $this;
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
     * @return $this
     */
    public function setAssignedDateTime(DateTime $assignedDateTime)
    {
        $this->assignedDateTime = $assignedDateTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getFinishedById()
    {
        return $this->finishedById;
    }

    /**
     * @param int $finishedById
     * @return $this
     */
    public function setFinishedById(int $finishedById)
    {
        $this->finishedById = $finishedById;

        return $this;
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
     * @return $this
     */
    public function setFinishedDateTime(DateTime $finishedDateTime)
    {
        $this->finishedDateTime = $finishedDateTime;

        return $this;
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
     * @return $this
     */
    public function setDonorName(string $donorName)
    {
        $this->donorName = $donorName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountHash(): string
    {
        return $this->accountHash;
    }

    /**
     * @param string $accountHash
     * @return $this
     */
    public function setAccountHash(string $accountHash): Claim
    {
        $this->accountHash = $accountHash;

        return $this;
    }

    /**
     * @return Poa[]
     */
    public function getPoas()
    {
        return $this->poas;
    }

    /**
     * @param Poa[] $poas
     * @return $this
     */
    public function setPoas(array $poas): Claim
    {
        $this->poas = $poas;

        return $this;
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
     * @return $this
     */
    public function setNoSiriusPoas(bool $noSiriusPoas): Claim
    {
        $this->noSiriusPoas = $noSiriusPoas;

        return $this;
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
     * @return $this
     */
    public function setNoMerisPoas(bool $noMerisPoas): Claim
    {
        $this->noMerisPoas = $noMerisPoas;

        return $this;
    }

    /**
     * @return string
     */
    public function getRejectionReason(): string
    {
        return $this->rejectionReason;
    }

    /**
     * @param string $rejectionReason
     * @return $this
     */
    public function setRejectionReason(string $rejectionReason): Claim
    {
        $this->rejectionReason = $rejectionReason;

        return $this;
    }

    /**
     * @return string
     */
    public function getRejectionReasonDescription(): string
    {
        return $this->rejectionReasonDescription;
    }

    /**
     * @param string $rejectionReasonDescription
     * @return $this
     */
    public function setRejectionReasonDescription(string $rejectionReasonDescription): Claim
    {
        $this->rejectionReasonDescription = $rejectionReasonDescription;

        return $this;
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
     * @return $this
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @return Note[]
     */
    public function getNotes(): array
    {
        return $this->notes;
    }

    /**
     * @param Note[] $notes
     * @return $this
     */
    public function setNotes(array $notes): Claim
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccountHashCount()
    {
        return $this->accountHashCount;
    }

    /**
     * @param int $accountHashCount
     * @return $this
     */
    public function setAccountHashCount(int $accountHashCount): Claim
    {
        $this->accountHashCount = $accountHashCount;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * @param bool $readOnly
     * @return $this
     */
    public function setReadOnly(bool $readOnly): Claim
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    /**
     * Map properties to correct types
     *
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    protected function map($property, $value)
    {
        switch ($property) {
            case 'application':
                return (($value instanceof Application || is_null($value)) ? $value : new Application($value));
            case 'poas':
                return array_map(function ($value) {
                    return ($value instanceof Poa ? $value : new Poa($value));
                }, $value);
            case 'payment':
                return (($value instanceof Payment || is_null($value)) ? $value : new Payment($value));
            case 'notes':
                return array_map(function ($value) {
                    return ($value instanceof Note ? $value : new Note($value));
                }, $value);
            case 'createdDateTime':
            case 'updatedDateTime':
            case 'receivedDateTime':
            case 'assignedDateTime':
            case 'finishedDateTime':
                return (($value instanceof DateTime || is_null($value)) ? $value : new DateTime($value));
            default:
                return parent::map($property, $value);
        }
    }
}
