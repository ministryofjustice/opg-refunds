<?php

namespace App\Service;

use App\Entity\Cases\User as UserEntity;
use App\Exception\AlreadyExistsException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;

/**
 * Class User
 * @package App\Service
 */
class User
{
    use EntityToModelTrait;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * User constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(UserEntity::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Get all users
     *
     * @return UserModel[]
     */
    public function getAll()
    {
        /** @var UserEntity[] $users */
        $users = $this->repository->findBy([], ['name' => 'ASC']);

        return $this->translateToDataModelArray($users);
    }

    /**
     * Get a specific user
     *
     * @param int $id
     * @return UserModel
     */
    public function getById(int $id)
    {
        $user = $this->getUserEntity($id);

        return $this->translateToDataModel($user);
    }

    /**
     * Get a specific user
     *
     * @param string $token
     * @return UserModel
     */
    public function getByToken(string $token)
    {
        /** @var UserEntity $user */
        $user = $this->repository->findOneBy([
            'token' => $token,
        ]);

        return $this->translateToDataModel($user);
    }

    /**
     * Add a user
     *
     * @param UserModel $userModel
     * @return UserModel
     */
    public function add(UserModel $userModel)
    {
        $this->checkForExisting($userModel->getEmail());

        $user = new UserEntity();
        $user->setFromDataModel($userModel);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->translateToDataModel($user);
    }

    /**
     * @param $userId
     * @param $name
     */
    public function setName($userId, $name)
    {
        $user = $this->getUserEntity($userId);

        $user->setName($name);

        $this->entityManager->flush();
    }

    /**
     * @param $userId
     * @param $email
     */
    public function setEmail($userId, $email)
    {
        $this->checkForExisting($email, $userId);

        $user = $this->getUserEntity($userId);

        $user->setEmail($email);

        $this->entityManager->flush();
    }

    /**
     * @param $userId
     * @param $roles
     */
    public function setRoles($userId, $roles)
    {
        $user = $this->getUserEntity($userId);

        //  Translate roles array to string for entity
        $roles = implode(',', $roles);

        $user->setRoles($roles);

        $this->entityManager->flush();
    }

    /**
     * @param $userId
     * @param $status
     */
    public function setStatus($userId, $status)
    {
        $user = $this->getUserEntity($userId);

        $user->setStatus($status);

        $this->entityManager->flush();
    }

    /**
     * Check for an existing user using this email address - excluding the user if an ID value is provided
     *
     * @param string $email
     * @param int $userId
     * @throws AlreadyExistsException
     */
    private function checkForExisting(string $email, int $userId = null)
    {
        //  First check that the email address is not already being used
        $existingUser = $this->repository->findOneBy([
            'email' => $email,
        ]);

        if ($existingUser instanceof UserEntity && $existingUser->getId() !== $userId) {
            throw new AlreadyExistsException('Email address already exists');
        }
    }

    /**
     * @param $id
     * @return UserEntity
     */
    private function getUserEntity($id): UserEntity
    {
        /** @var UserEntity $user */
        $user = $this->repository->findOneBy([
            'id' => $id,
        ]);

        return $user;
    }
}
