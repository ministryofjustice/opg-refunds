<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="verification")
 **/
class Verification extends AbstractEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $passes;

    /**
     * @var Claim
     * @ORM\OneToOne(targetEntity="RefundCase", inversedBy="verification")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id")
     */
    protected $case;

    /**
     * @var Poa
     * @ORM\OneToOne(targetEntity="Poa")
     * @ORM\JoinColumn(name="poa_id", referencedColumnName="id")
     */
    protected $poa;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $poaId
     */
    public function setPoaId(int $poaId)
    {
        $this->poaId = $poaId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isPasses(): bool
    {
        return $this->passes;
    }

    /**
     * @param bool $passes
     */
    public function setPasses(bool $passes)
    {
        $this->passes = $passes;
    }

    /**
     * @return Claim
     */
    public function getCase(): Claim
    {
        return $this->case;
    }

    /**
     * @param Claim $case
     */
    public function setCase(Claim $case)
    {
        $this->case = $case;
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