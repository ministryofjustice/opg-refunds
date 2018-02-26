<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment as PaymentModel;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="payment", indexes={
 * @ORM\Index(name="idx_payment_amount", columns={"amount"}),
 * @ORM\Index(name="idx_payment_method", columns={"method"}),
 * @ORM\Index(name="idx_payment_added_datetime", columns={"added_datetime"})
 * })
 **/
class Payment extends AbstractEntity
{
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
     * @var string
     * @ORM\Column(name="spreadsheet_hash", type="string", nullable=true)
     */
    protected $spreadsheetHash;

    /**
     * @var Claim
     * @ORM\OneToOne(targetEntity="Claim", mappedBy="payment")
     */
    protected $claim;

    public function __construct(float $amount, string $method)
    {
        $this->amount = $amount;
        $this->method = $method;

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
     * @return string
     */
    public function getSpreadsheetHash(): string
    {
        return $this->spreadsheetHash;
    }

    /**
     * @param string $spreadsheetHash
     */
    public function setSpreadsheetHash(string $spreadsheetHash)
    {
        $this->spreadsheetHash = $spreadsheetHash;
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
    public function getAsDataModel(array $modelToEntityMappings = [], string $dataModelClass = PaymentModel::class)
    {
        return parent::getAsDataModel($modelToEntityMappings, $dataModelClass);
    }
}