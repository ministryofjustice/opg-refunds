<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

/**
 * Class Verification
 * @package Opg\Refunds\Caseworker\DataModel\Cases
 */
class Verification extends AbstractDataModel
{
    const TYPE_ATTORNEY = 'attorney';
    const TYPE_ATTORNEY_NAME = 'attorney-name';
    const TYPE_ATTORNEY_DOB = 'attorney-dob';
    const TYPE_CASE_NUMBER = 'case-number';
    const TYPE_DONOR_POSTCODE = 'donor-postcode';
    const TYPE_ATTORNEY_POSTCODE = 'attorney-postcode';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $passes;

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
    public function setId(int $id): Verification
    {
        $this->id = $id;

        return $this;
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
     * @return $this
     */
    public function setType(string $type): Verification
    {
        $this->type = $type;

        return $this;
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
     * @return $this
     */
    public function setPasses(bool $passes): Verification
    {
        $this->passes = $passes;

        return $this;
    }
}