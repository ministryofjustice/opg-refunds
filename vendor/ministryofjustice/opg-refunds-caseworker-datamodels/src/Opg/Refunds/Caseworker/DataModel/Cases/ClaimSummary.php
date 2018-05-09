<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use DateTime;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;

class ClaimSummary extends AbstractDataModel
{
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
    protected $updatedDateTime;

    /**
     * @var DateTime
     */
    protected $receivedDateTime;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var int
     */
    protected $assignedToId;

    /**
     * @var string
     */
    protected $assignedToName;

    /**
     * @var string
     */
    protected $assignedToStatus;

    /**
     * @var int
     */
    protected $finishedById;

    /**
     * @var string
     */
    protected $finishedByName;

    /**
     * @var string
     */
    protected $finishedByStatus;

    /**
     * @var DateTime
     */
    protected $finishedDateTime;

    /**
     * @var string
     */
    protected $donorName;

    /**
     * @var bool;
     */
    protected $assistedDigital;

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
     * @return string
     */
    public function getAssignedToName()
    {
        return $this->assignedToName;
    }

    /**
     * @param string $assignedToName
     * @return $this
     */
    public function setAssignedToName(string $assignedToName): ClaimSummary
    {
        $this->assignedToName = $assignedToName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAssignedToStatus()
    {
        return $this->assignedToStatus;
    }

    /**
     * @param string $assignedToStatus
     * @return $this
     */
    public function setAssignedToStatus(string $assignedToStatus): ClaimSummary
    {
        $this->assignedToStatus = $assignedToStatus;

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
    public function setFinishedByName(string $finishedByName): ClaimSummary
    {
        $this->finishedByName = $finishedByName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFinishedByStatus()
    {
        return $this->finishedByStatus;
    }

    /**
     * @param string $finishedByStatus
     * @return $this
     */
    public function setFinishedByStatus(string $finishedByStatus): ClaimSummary
    {
        $this->finishedByStatus = $finishedByStatus;

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
     * @return bool
     */
    public function isAssistedDigital(): bool
    {
        return $this->assistedDigital;
    }

    /**
     * @param bool $assistedDigital
     * @return $this
     */
    public function setAssistedDigital(bool $assistedDigital)
    {
        $this->assistedDigital = $assistedDigital;

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
            case 'updatedDateTime':
            case 'receivedDateTime':
            case 'finishedDateTime':
                return (($value instanceof DateTime || is_null($value)) ? $value : new DateTime($value));
            default:
                return parent::map($property, $value);
        }
    }
}