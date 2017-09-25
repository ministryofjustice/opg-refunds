<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;
use DateTime;

/**
 * Class Claim
 * @package Opg\Refunds\Caseworker\DataModel\Cases
 */
class Claim extends AbstractDataModel
{
    const STATUS_NEW = 'new';
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
     * @var DateTime
     */
    protected $finishedDateTime;

    /**
     * @var string
     */
    protected $donorName;

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
     * @var Payment
     */
    protected $payment;

    /**
     * @var Log[]
     */
    protected $logs;

    /**
     * @var int
     */
    protected $accountHashCount;

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
     * @return Poa[]
     */
    public function getPoas(): array
    {
        return $this->poas;
    }

    /**
     * @param Poa[] $poas
     * @return Claim
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
     * @return Claim
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
     * @return Claim
     */
    public function setNoMerisPoas(bool $noMerisPoas): Claim
    {
        $this->noMerisPoas = $noMerisPoas;

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
     * @return Log[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * @param Log[] $logs
     * @return Claim
     */
    public function setLogs(array $logs): Claim
    {
        $this->logs = $logs;
        
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
     * @return Claim
     */
    public function setAccountHashCount(int $accountHashCount): Claim
    {
        $this->accountHashCount = $accountHashCount;

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
            case 'logs':
                return array_map(function ($value) {
                    return ($value instanceof Log ? $value : new Log($value));
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
