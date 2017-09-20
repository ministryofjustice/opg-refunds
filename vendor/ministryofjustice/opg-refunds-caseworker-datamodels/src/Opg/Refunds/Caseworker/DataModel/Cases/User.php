<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;

/**
 * Class User
 * @package Opg\Refunds\Caseworker\DataModel\Cases
 */
class User extends AbstractDataModel
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
     * @var Claim[]
     */
    protected $claims;

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
    public function getClaims()
    {
        return $this->claims;
    }

    /**
     * @param array $claims
     * @return $this
     */
    public function setClaims(array $claims)
    {
        $this->claims = $claims;

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
            case 'claims':
                return array_map(function ($value) {
                    return ($value instanceof Claim ? $value : new Claim($value));
                }, $value);
            default:
                return parent::map($property, $value);
        }
    }
}
