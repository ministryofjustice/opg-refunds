<?php

namespace App\Entity\Cases;

use App\DataModel\Cases\RefundCase as CaseDataModel;
use App\Entity\AbstractEntity;
use App\Service\IdentFormatter;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="cases")
 **/
class RefundCase extends AbstractEntity //Case is a reserved word in PHP 7
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
     * @var resource
     * @ORM\Column(name="json_data", type="binary")
     */
    protected $jsonData;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @var Caseworker
     * @ORM\ManyToOne(targetEntity="Caseworker", inversedBy="assignedCases")
     * @ORM\JoinColumn(name="assigned_to_id", referencedColumnName="id", nullable=true)
     */
    protected $assignedTo;

    /**
     * @var DateTime
     * @ORM\Column(name="assigned_datetime", type="datetimetz", nullable=true)
     */
    protected $assignedDateTime;

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
     * @var Poa[]
     * @ORM\OneToMany(targetEntity="Poa", mappedBy="case")
     */
    protected $poas;

    /**
     * @var Verification
     * @ORM\OneToOne(targetEntity="Verification", mappedBy="case")
     */
    protected $verification;

    /**
     * @var Payment
     * @ORM\OneToOne(targetEntity="Payment", mappedBy="case")
     */
    protected $payment;

    public function __construct(int $id, DateTime $receivedDateTime, string $jsonData, string $donorName)
    {
        $this->id = $id;
        $this->receivedDateTime = $receivedDateTime;
        $this->jsonData = $jsonData;
        $this->donorName = $donorName;

        $this->createdDateTime = new DateTime();
        $this->status = CaseDataModel::STATUS_NEW;
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
    public function getUpdatedDateTime(): DateTime
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
     * @return string
     */
    public function getJsonData()
    {
        return stream_get_contents($this->jsonData);
    }

    /**
     * @param string $jsonData
     */
    public function setJsonData(string $jsonData)
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
     * @return Caseworker
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    /**
     * @param Caseworker $assignedTo
     */
    public function setAssignedTo(Caseworker $assignedTo)
    {
        $this->assignedTo = $assignedTo;
    }

    /**
     * @return DateTime
     */
    public function getAssignedDateTime(): DateTime
    {
        return $this->assignedDateTime;
    }

    /**
     * @param DateTime $assignedDateTime
     */
    public function setAssignedDateTime(DateTime $assignedDateTime)
    {
        $this->assignedDateTime = $assignedDateTime;
    }

    /**
     * @return DateTime
     */
    public function getFinishedDateTime(): DateTime
    {
        return $this->finishedDateTime;
    }

    /**
     * @param DateTime $finishedDateTime
     */
    public function setFinishedDateTime(DateTime $finishedDateTime)
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
     * @return Poa[]
     */
    public function getPoas(): array
    {
        return $this->poas;
    }

    /**
     * @param Poa[] $poas
     */
    public function setPoas(array $poas)
    {
        $this->poas = $poas;
    }

    /**
     * @return Verification
     */
    public function getVerification(): Verification
    {
        return $this->verification;
    }

    /**
     * @param Verification $verification
     */
    public function setVerification(Verification $verification)
    {
        $this->verification = $verification;
    }

    /**
     * @return Payment
     */
    public function getPayment(): Payment
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

    public function toArray($excludeProperties = ['assignedTo'], $includeChildren = ['poas']): array
    {
        $caseArray = parent::toArray($excludeProperties, $includeChildren);
        $caseArray['referenceNumber'] = IdentFormatter::format($this->getId());
        return $caseArray;
    }
}