<?php

namespace App\Entity\Cases;

use App\Entity\AbstractEntity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;

/**
 * @ORM\Entity @ORM\Table(name="`user`")
 **/
class User extends AbstractEntity
{
    /**
     * Class of the datamodel that this entity can be converted to
     *
     * @var string
     */
    protected $dataModelClass = UserModel::class;

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
     * @ORM\Column(name="password_hash", type="string", nullable=true)
     */
    protected $passwordHash;

    /**
     * @var string
     * @ORM\Column(type="string")
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
     * @var Collection|Claim[]
     * @ORM\OneToMany(targetEntity="Claim", mappedBy="assignedTo")
     * @ORM\OrderBy({"updatedDateTime" = "ASC"})
     */
    protected $assignedClaims;

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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
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
    public function getToken()
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
     * @return Collection|Claim[]
     */
    public function getAssignedClaims()
    {
        return $this->assignedClaims;
    }

    /**
     * @param Collection|Claim[] $assignedClaims
     */
    public function setAssignedClaims($assignedClaims)
    {
        $this->assignedClaims = $assignedClaims;
    }

    /**
     * Returns the entity as a datamodel structure
     *
     * In the $modelToEntityMappings array key values reflect the set method to be used in the datamodel
     * for example a mapping of 'Something' => 'AnotherThing' will result in $model->setSomething($entity->getAnotherThing());
     * The value in the mapping array can also be a callback function
     *
     * @param array $modelToEntityMappings
     * @return AbstractDataModel
     */
    public function getAsDataModel(array $modelToEntityMappings = [])
    {
        $modelToEntityMappings = array_merge($modelToEntityMappings, [
            'Claims' => 'AssignedClaims',
        ]);

        return parent::getAsDataModel($modelToEntityMappings);
    }
}
