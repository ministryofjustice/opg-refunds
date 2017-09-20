<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

/**
 * Class Caseworker
 * @package Opg\Refunds\Caseworker\DataModel\Cases
 */
class Caseworker extends AbstractDataModel
{
    const ROLE_CASEWORKER = 'Caseworker';
    const ROLE_REPORTING  = 'Reporting';
    const ROLE_REFUND     = 'RefundManager';
    const ROLE_ADMIN      = 'Admin';

    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $roles;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var RefundCase[]
     */
    protected $refundCases;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

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
     * @return string
     */
    public function getRoles(): string
    {
        return $this->roles;
    }

    /**
     * @param string $roles
     * @return $this
     */
    public function setRoles(string $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return array
     */
    public function getRefundCases()
    {
        return $this->refundCases;
    }

    /**
     * @param array $refundCases
     * @return $this
     */
    public function setRefundCases(array $refundCases)
    {
        $this->refundCases = $refundCases;

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
            case 'refundCases':
                return array_map(function ($value) {
                    return ($value instanceof RefundCase ? $value : new RefundCase($value));
                }, $value);
            default:
                return parent::map($property, $value);
        }
    }
}
