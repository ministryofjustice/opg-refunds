<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment as PaymentModel;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="payment")
 **/
class Payment extends AbstractEntity
{
    /**
     * Class of the datamodel that this entity can be converted to
     *
     * @var string
     */
    protected $dataModelClass = PaymentModel::class;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var float
     * @ORM\Column(type="float")
     */
    protected $amount;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $method;

    /**
     * @var DateTime
     * @ORM\Column(name="added_datetime", type="datetimetz")
     */
    protected $addedDateTime;

    /**
     * @var DateTime
     * @ORM\Column(name="processed_datetime", type="datetimetz", nullable=true)
     */
    protected $processedDateTime;

    /**
     * @var Claim
     * @ORM\OneToOne(targetEntity="Claim", mappedBy="payment")
     */
    protected $claim;

    public function __construct(float $amount, string $method, Claim $claim)
    {
        $this->amount = $amount;
        $this->method = $method;
        $this->claim = $claim;

        $this->addedDateTime = new DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * @return DateTime
     */
    public function getAddedDateTime(): DateTime
    {
        return $this->addedDateTime;
    }

    /**
     * @param DateTime $addedDateTime
     */
    public function setAddedDateTime(DateTime $addedDateTime)
    {
        $this->addedDateTime = $addedDateTime;
    }

    /**
     * @return DateTime
     */
    public function getProcessedDateTime()
    {
        return $this->processedDateTime;
    }

    /**
     * @param DateTime $processedDateTime
     */
    public function setProcessedDateTime(DateTime $processedDateTime)
    {
        $this->processedDateTime = $processedDateTime;
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