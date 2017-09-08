<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="caseworker")
 **/
class Caseworker extends AbstractEntity
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
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(name="password_hash", type="string")
     */
    protected $passwordHash;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $status;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $roles;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $token;

    /**
     * @var int
     * @ORM\Column(name="token_expires", type="integer", nullable=true)
     */
    protected $tokenExpires;

    /**
     * @var RefundCase[]
     * @ORM\OneToMany(targetEntity="RefundCase", mappedBy="$assignedCases")
     */
    protected $assignedCases;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     */
    public function setName(string $name)
    {
        $this->name = $name;
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
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
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
     */
    public function setPasswordHash(string $passwordHash)
    {
        $this->passwordHash = $passwordHash;
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
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
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
     */
    public function setRoles(string $roles)
    {
        $this->roles = $roles;
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
     */
    public function setToken(string $token)
    {
        $this->token = $token;
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
     */
    public function setTokenExpires(int $tokenExpires)
    {
        $this->tokenExpires = $tokenExpires;
    }

    /**
     * @return RefundCase[]
     */
    public function getAssignedCases(): array
    {
        return $this->assignedCases;
    }

    /**
     * @param RefundCase[] $assignedCases
     */
    public function setAssignedCases(array $assignedCases)
    {
        $this->assignedCases = $assignedCases;
    }

    public function toArray($excludeProperties = ['passwordHash']): array
    {
        return parent::toArray($excludeProperties);
    }
}