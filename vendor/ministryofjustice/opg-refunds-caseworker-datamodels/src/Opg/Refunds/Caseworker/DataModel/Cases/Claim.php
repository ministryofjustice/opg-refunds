<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification as VerificationModel;
use DateTime;
use Opg\Refunds\Caseworker\DataModel\MoneyFormatter;
use Opg\Refunds\Caseworker\DataModel\StatusFormatter;

/**
 * Class Claim
 * @package Opg\Refunds\Caseworker\DataModel\Cases
 */
class Claim extends AbstractDataModel
{
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DUPLICATE = 'duplicate';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ACCEPTED = 'accepted';

    const REJECTION_REASON_NO_ELIGIBLE_POAS_FOUND = 'noEligiblePoasFound';
    const REJECTION_REASON_PREVIOUSLY_REFUNDED = 'previouslyRefunded';
    const REJECTION_REASON_NO_FEES_PAID = 'noFeesPaid';
    const REJECTION_REASON_CLAIM_NOT_VERIFIED = 'claimNotVerified';
    const REJECTION_REASON_OTHER = 'other';

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
     * @var string
     */
    protected $finishedByName;

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
     * @var bool
     */
    protected $outcomeEmailSent;

    /**
     * @var bool
     */
    protected $outcomeTextSent;

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
     * @var array
     */
    protected $duplicateOfIds;

    /**
     * @var array
     */
    protected $duplicateClaimIds;

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
     * @return string
     */
    public function getFinishedByName()
    {
        return $this->finishedByName;
    }

    /**
     * @param string $finishedByName
     * @return $this
     */
    public function setFinishedByName(string $finishedByName)
    {
        $this->finishedByName = $finishedByName;

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
    public function getAccountHash()
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
     * @return bool
     */
    public function hasAccountHash(): bool
    {
        return $this->accountHash !== null;
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
    public function getRejectionReason()
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
    public function getRejectionReasonDescription()
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
     * @return bool
     */
    public function isOutcomeEmailSent(): bool
    {
        return $this->outcomeEmailSent;
    }

    /**
     * @param bool $outcomeEmailSent
     * @return $this
     */
    public function setOutcomeEmailSent(bool $outcomeEmailSent)
    {
        $this->outcomeEmailSent = $outcomeEmailSent;

        return $this;
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
     * @return $this
     */
    public function setOutcomeTextSent(bool $outcomeTextSent)
    {
        $this->outcomeTextSent = $outcomeTextSent;

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
    public function hasAccountHashCount(): bool
    {
        return $this->accountHashCount !== null;
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
     * @return array
     */
    public function getDuplicateOfIds(): array
    {
        return $this->duplicateOfIds;
    }

    /**
     * @param array $duplicateOfIds
     * @return $this
     */
    public function setDuplicateOfIds(array $duplicateOfIds): Claim
    {
        $this->duplicateOfIds = $duplicateOfIds;

        return $this;
    }

    /**
     * @return array
     */
    public function getDuplicateClaimIds(): array
    {
        return $this->duplicateClaimIds;
    }

    /**
     * @param array $duplicateClaimIds
     * @return $this
     */
    public function setDuplicateClaimIds(array $duplicateClaimIds): Claim
    {
        $this->duplicateClaimIds = $duplicateClaimIds;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPoas()
    {
        return $this->getPoas() !== null && count($this->getPoas()) > 0;
    }

    /**
     * @return bool
     */
    public function hasSiriusPoas()
    {
        return $this->hasSystemPoas(PoaModel::SYSTEM_SIRIUS);
    }

    /**
     * @return bool
     */
    public function hasMerisPoas()
    {
        return $this->hasSystemPoas(PoaModel::SYSTEM_MERIS);
    }

    /**
     * @return array
     */
    public function getSiriusPoas()
    {
        return $this->getSystemPoas(PoaModel::SYSTEM_SIRIUS);
    }

    /**
     * @return array
     */
    public function getMerisPoas()
    {
        return $this->getSystemPoas(PoaModel::SYSTEM_MERIS);
    }

    /**
     * @return bool
     */
    public function isAttorneyVerified(): bool
    {
        return $this->isVerified(VerificationModel::TYPE_ATTORNEY);
    }

    /**
     * @return bool
     */
    public function isCaseNumberVerified(): bool
    {
        return $this->isVerified(VerificationModel::TYPE_CASE_NUMBER);
    }

    /**
     * @return bool
     */
    public function isDonorPostcodeVerified(): bool
    {
        return $this->isVerified(VerificationModel::TYPE_DONOR_POSTCODE);
    }

    /**
     * @return bool
     */
    public function isAttorneyPostcodeVerified(): bool
    {
        return $this->isVerified(VerificationModel::TYPE_ATTORNEY_POSTCODE);
    }

    /**
     * @return bool
     */
    public function isClaimVerified()
    {
        $verificationCount = 0;

        if ($this->isAttorneyVerified()) {
            //Means that both the attorney's name and dob have been verified so counts for 2
            $verificationCount+=2;
        }

        if ($this->isCaseNumberVerified()) {
            $verificationCount++;
        }

        if ($this->isDonorPostcodeVerified()) {
            $verificationCount++;
        }

        if ($this->isAttorneyPostcodeVerified()) {
            $verificationCount++;
        }

        return $verificationCount >= 3;
    }

    /**
     * @return bool
     */
    public function isClaimComplete()
    {
        return $this->allPoasComplete()
            && ($this->isNoSiriusPoas() || $this->hasSiriusPoas())
            && ($this->isNoMerisPoas() || $this->hasMerisPoas());
    }

    /**
     * @return float
     */
    public function getRefundTotalAmount()
    {
        $refundTotalAmount = 0.0;

        foreach ($this->getPoas() as $poa) {
            $refundTotalAmount += $poa->getRefundAmount() + $poa->getRefundInterestAmount();
        }

        return $refundTotalAmount;
    }

    /**
     * @return string
     */
    public function getRefundTotalAmountString(): string
    {
        if ($this->getPoas() === null) {
            return '£0.00';
        }

        return MoneyFormatter::getMoneyString($this->getRefundTotalAmount());
    }

    /**
     * @return float
     */
    public function getRefundInterestAmount()
    {
        $refundInterestAmount = 0.0;

        foreach ($this->getPoas() as $poa) {
            $refundInterestAmount += $poa->getRefundInterestAmount();
        }

        return $refundInterestAmount;
    }

    /**
     * @return string
     */
    public function getRefundInterestAmountString(): string
    {
        if ($this->getPoas() === null) {
            return '£0.00';
        }

        return MoneyFormatter::getMoneyString($this->getRefundInterestAmount());
    }

    /**
     * @return bool
     */
    public function isClaimRefundNonZero()
    {
        return $this->getRefundTotalAmount() > 0;
    }

    /**
     * @return bool
     */
    private function allPoasComplete(): bool
    {
        if ($this->getPoas() === null) {
            return true;
        }

        foreach ($this->getPoas() as $poa) {
            if (!$poa->isComplete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $system
     * @return bool
     */
    private function hasSystemPoas(string $system)
    {
        if ($this->getPoas() === null) {
            return false;
        }

        foreach ($this->getPoas() as $poa) {
            if ($poa->getSystem() === $system) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $system
     * @return array
     */
    private function getSystemPoas(string $system)
    {
        $poas = [];

        if ($this->getPoas() === null) {
            return $poas;
        }

        foreach ($this->getPoas() as $poa) {
            if ($poa->getSystem() === $system) {
                $poas[] = $poa;
            }
        }

        return $poas;
    }

    /**
     * @param string $verificationType
     * @return bool
     */
    private function isVerified(string $verificationType): bool
    {
        if ($this->getPoas() === null) {
            return false;
        }

        foreach ($this->getPoas() as $poa) {
            if ($poa->isVerified($verificationType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getStatusText(): string
    {
        return StatusFormatter::getStatusText($this->getStatus());
    }

    /**
     * @param string $type
     * @return Note[]
     */
    public function getNotesOfType(string $type): array
    {
        $notes = [];

        if ($this->getNotes() !== null) {
            foreach ($this->getNotes() as $note) {
                if ($note->getType() === $type) {
                    $notes[] = $note;
                }
            }
        }

        return $notes;
    }

    /**
     * @return bool
     */
    public function canChangeOutcome(): bool
    {
        return ($this->getStatus() === ClaimModel::STATUS_ACCEPTED && $this->getPayment() === null)
            || $this->getStatus() === ClaimModel::STATUS_REJECTED;
    }

    /**
     * @return bool
     */
    public function canReassignClaim(): bool
    {
        return $this->getStatus() === ClaimModel::STATUS_IN_PROGRESS;
    }

    /**
     * @return bool
     */
    public function canResolveAsDuplicate(): bool
    {
        return $this->getStatus() === ClaimModel::STATUS_IN_PROGRESS && !$this->hasPoas()
            && !$this->isNoSiriusPoas() && !$this->isNoMerisPoas();
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
