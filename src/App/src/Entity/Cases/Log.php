<?php

namespace App\Entity\Cases;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="log")
 **/
class Log
{
    /**
     * @var int
     * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var DateTime
     * @ORM\Column(name="created_datetime", type="datetime")
     */
    private $createdDateTime;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $message;

    /**
     * @var RefundCase
     * @ORM\ManyToOne(targetEntity="RefundCase")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id")
     */
    private $case;

    /**
     * @var Caseworker
     * @ORM\ManyToOne(targetEntity="Caseworker")
     * @ORM\JoinColumn(name="caseworker_id", referencedColumnName="id")
     */
    private $caseworker;

    /**
     * @var Poa
     * @ORM\ManyToOne(targetEntity="Poa")
     * @ORM\JoinColumn(name="poa_id", referencedColumnName="id")
     */
    private $poa;

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
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return RefundCase
     */
    public function getCase(): RefundCase
    {
        return $this->case;
    }

    /**
     * @param RefundCase $case
     */
    public function setCase(RefundCase $case)
    {
        $this->case = $case;
    }

    /**
     * @return Caseworker
     */
    public function getCaseworker(): Caseworker
    {
        return $this->caseworker;
    }

    /**
     * @param Caseworker $caseworker
     */
    public function setCaseworker(Caseworker $caseworker)
    {
        $this->caseworker = $caseworker;
    }

    /**
     * @return Poa
     */
    public function getPoa(): Poa
    {
        return $this->poa;
    }

    /**
     * @param Poa $poa
     */
    public function setPoa(Poa $poa)
    {
        $this->poa = $poa;
    }
}