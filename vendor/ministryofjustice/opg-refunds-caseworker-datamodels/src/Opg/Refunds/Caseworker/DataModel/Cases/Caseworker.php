<?php

namespace Opg\Refunds\Caseworker\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use DateTime;

/**
 * Class Caseworker
 * @package Opg\Refunds\Caseworker\DataModel\Cases
 */
class Caseworker extends AbstractDataModel
{
    const CASEWORKER_ROLE_CASEWORKER = 'Cashworker';
    const CASEWORKER_ROLE_REPORTING  = 'Reporting';
    const CASEWORKER_ROLE_REFUND     = 'RefundManager';
    const CASEWORKER_ROLE_ADMIN      = 'Admin';

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
    protected $passwordHash;

    /**
     * @var int
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
     * @var int
     */
    protected $tokenExpires;

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
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @param string $passwordHash
     * @return $this
     */
    public function setPasswordHash(string $passwordHash)
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status)
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
     * @return int
     */
    public function getTokenExpires(): int
    {
        return $this->tokenExpires;
    }

    /**
     * @param int $tokenExpires
     * @return $this
     */
    public function setTokenExpires(int $tokenExpires)
    {
        $this->tokenExpires = $tokenExpires;

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

    /**
     * Returns $this as an array - exclude the fields in the filter array if provided
     *
     * @param array $excludeFilter
     * @return array
     */
    public function toArray(array $excludeFilter = [])
    {
        //  For security reasons don't extract the password hash value to an array
        //  If that value is required it can be purposefully retrieved using the get method above
        return parent::toArray(array_merge($excludeFilter, [
            'password-hash',
        ]));
    }
}
