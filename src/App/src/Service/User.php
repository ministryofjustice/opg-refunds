<?php

namespace App\Service;

use App\Entity\Cases\User as UserEntity;
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
