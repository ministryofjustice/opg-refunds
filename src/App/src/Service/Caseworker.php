<?php

namespace App\Service;

use App\Entity\Cases\Caseworker as CaseworkerEntity;
use App\Exception\InvalidInputException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Caseworker\DataModel\Cases\Caseworker as CaseworkerModel;

/**
 * Class Caseworker
 * @package App\Service
 */
class Caseworker
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Caseworker constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(CaseworkerEntity::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Find a caseworker by ID
     *
     * @param int $id
     * @return CaseworkerModel
     * @throws InvalidInputException
     */
    public function findById(int $id)
    {
        /** @var CaseworkerEntity $caseworker */
        $caseworker = $this->repository->findOneById($id);

        if (!$caseworker instanceof CaseworkerEntity) {
            throw new InvalidInputException('Caseworker not found');
        }

        return $caseworker->getAsDataModel();
    }

    /**
     * Find a caseworker by a set of credentials - used by authentication
     *
     * @param string $email
     * @param string $password
     * @return CaseworkerModel
     * @throws InvalidInputException
     */
    public function findByCredentials(string $email, string $password)
    {
        /** @var CaseworkerEntity $caseworker */
        $caseworker = $this->repository->findOneByEmail($email);

        if (!$caseworker instanceof CaseworkerEntity || $caseworker->getPasswordHash() != hash('sha256', $password)) {
            throw new InvalidInputException('Caseworker not found');
        }

        return $caseworker->getAsDataModel();
    }

    /**
     * Find a caseworker by a request token value - used by authentication
     *
     * @param string $token
     * @return CaseworkerModel
     * @throws InvalidInputException
     */
    public function findByToken(string $token)
    {
        /** @var CaseworkerEntity $caseworker */
        $caseworker = $this->repository->findOneByToken($token);

        if (!$caseworker instanceof CaseworkerEntity) {
            throw new InvalidInputException('Caseworker not found');
        }

        return $caseworker->getAsDataModel();
    }

    /**
     * Set the token values against a caseworker
     *
     * @param int $id
     * @param string $token
     * @param int $tokenExpires
     * @return bool
     */
    public function setToken(int $id, string $token, int $tokenExpires)
    {
        /** @var CaseworkerEntity $caseworker */
        $caseworker = $this->entityManager->getReference(CaseworkerEntity::class, $id);

        $caseworker->setToken($token);
        $caseworker->setTokenExpires($tokenExpires);

        $this->entityManager->flush();

        return true;
    }
}
