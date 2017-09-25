<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification as VerificationModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="verification")
 **/
class Verification extends AbstractEntity
{
    /**
     * Class of the datamodel that this entity can be converted to
     *
     * @var string
     */
    protected $dataModelClass = VerificationModel::class;

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
     * @var Poa
     * @ORM\ManyToOne(targetEntity="Poa", inversedBy="verifications")
     * @ORM\JoinColumn(name="poa_id", referencedColumnName="id")
     */
    protected $poa;

    public function __construct(string $type, bool $passes, Poa $poa)
    {
        $this->type = $type;
        $this->passes = $passes;
        $this->poa = $poa;
    }

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