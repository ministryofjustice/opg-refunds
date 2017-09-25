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
        $users = $this->repository->findBy([]);

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
        /** @var UserEntity $user */
        $user = $this->repository->findOneBy([
            'id' => $id,
        ]);

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
}
