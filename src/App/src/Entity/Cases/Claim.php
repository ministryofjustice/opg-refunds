<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Applications\Application as ApplicationModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="claim")
 **/
class Claim extends AbstractEntity
{
    /**
     * Class of the datamodel that this entity can be converted to
     *
     * @var string
     */
    protected $dataModelClass = ClaimModel::class;

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
     * @var resource|string
     * @ORM\Column(name="json_data", type="binary")
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
     * @ORM\Column(name="account_hash", type="string")
     */
    protected $accountHash;

    /**
     * @var Poa[]
     * @ORM\OneToMany(targetEntity="Poa", mappedBy="claim", cascade={"persist", "remove"})
     */
    protected $poas;

    /**
     * @var Verification
     * @ORM\OneToOne(targetEntity="Verification", mappedBy="claim", cascade={"persist", "remove"})
     */
    protected $verification;

    /**
     * @var Payment
     * @ORM\OneToOne(targetEntity="Payment", mappedBy="claim", cascade={"persist", "remove"})
     */
    protected $payment;

    /**
     * @var Log[]
     * @ORM\OneToMany(targetEntity="Log", mappedBy="claim", cascade={"persist", "remove"})
     */
    protected $logs;

    /**
     * @var int
     */
    protected $accountHashCount;

    public function __construct(int $id, DateTime $receivedDateTime, string $jsonData, string $donorName, string $accountHash)
    {
        $this->id = $id;
        $this->receivedDateTime = $receivedDateTime;
        $this->jsonData = $jsonData;
        $this->donorName = $donorName;
        $this->accountHash = $accountHash;

        $this->createdDateTime = new DateTime();
        $this->status = ClaimModel::STATUS_NEW;
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
     * IMPORTANT - $this->jsonData is set as a PHP "resource" by Doctrine but leaving it like that means that
     * repeated calls to this function will yield different results (i.e. the first call will return the full
     * string and subsequent calls will return a blank string). Therefore on the first call the resource is set
     * to a proper string value.
     *
     * @return string
     */
    public function getJsonData()
    {
        if (is_resource($this->jsonData)) {
            $this->jsonData = stream_get_contents($this->jsonData);
        }

        return $this->jsonData;
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
     * @return User
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    /**
     * @param User $assignedTo
     */
    public function setAssignedTo(User $assignedTo)
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
    public function setAssignedDateTime(DateTime $assignedDateTime)
    {
        $this->assignedDateTime = $assignedDateTime;
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
     * @return string
     */
    public function getAccountHash(): string
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
     * @return Poa[]
     */
    public function getPoas()
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
    public function getVerification()
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
     * @return Log[]
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @param Log[] $logs
     */
    public function setLogs($logs)
    {
        $this->logs = $logs;
    }

    /**
     * @param Log $log
     */
    public function addLog(Log $log)
    {
        $this->logs[] = $log;
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
     */
    public function setAccountHashCount(int $accountHashCount)
    {
        $this->accountHashCount = $accountHashCount;
    }

    /**
     * Returns the entity as a datamodel structure
     *
     * In the $modelToEntityMappings array key values reflect the set method to be used in the datamodel
     * for example a mapping of 'Something' => 'AnotherThing' will result in $model->setSomething($entity->getAnotherThing());
     * The value in the mapping array can also be a callback function
     *
     * @param array $modelToEntityMappings
     * @return AbstractDataModel
     */
    public function getAsDataModel(array $modelToEntityMappings = [])
    {
        $modelToEntityMappings = array_merge($modelToEntityMappings, [
            'Application' => function () {
                return new ApplicationModel($this->getJsonData());
            },
            'AssignedToId' => function () {
                return ($this->getAssignedTo() instanceof User ? $this->getAssignedTo()->getId() : null);
            },
        ]);

        return parent::getAsDataModel($modelToEntityMappings);
    }
}
