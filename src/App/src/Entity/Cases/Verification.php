<?php

namespace App\Entity\Cases;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="verification")
 **/
class Verification
{
    /**
     * @var int
     * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="refund_case_id", type="integer")
     */
    private $refundCaseId;

    /**
     * @var int
     * @ORM\Column(name="poa_id", type="integer")
     */
    private $poaId;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $passes;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getRefundCaseId(): int
    {
        return $this->refundCaseId;
    }

    /**
     * @param int $refundCaseId
     */
    public function setRefundCaseId(int $refundCaseId)
    {
        $this->refundCaseId = $refundCaseId;
    }

    /**
     * @return int
     */
    public function getPoaId(): int
    {
        return $this->poaId;
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
}