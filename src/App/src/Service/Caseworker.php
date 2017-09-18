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
     * Get all caseworkers
     *
     * @return CaseworkerModel[]
     */
    public function getAll()
    {
        /** @var CaseworkerEntity[] $caseworkers */
        $caseworkers = $this->repository->findBy([]);

        return $this->translateToDataModelArray($caseworkers);
    }

    /**
     * Get a specific caseworker
     *
     * @param int $id
     * @return CaseworkerModel
     */
    public function getById(int $id)
    {
        /** @var CaseworkerEntity $caseworker */
        $caseworker = $this->repository->findOneBy([
            'id' => $id,
        ]);

        return $this->translateToDataModel($caseworker);
    }

    /**
     * Get a caseworker using a set of credentials - used by authentication
     *
     * @param string $email
     * @param string $password
     * @return CaseworkerModel
     * @throws InvalidInputException
     */
    public function getByCredentials(string $email, string $password)
    {
        /** @var CaseworkerEntity $caseworker */
        $caseworker = $this->repository->findOneBy([
            'email' => $email,
        ]);

        /** @var CaseworkerModel $caseworkerModel */
        $caseworkerModel = $this->translateToDataModel($caseworker);

        if ($caseworkerModel->getPasswordHash() != hash('sha256', $password)) {
            throw new InvalidInputException('Caseworker not found');
        }

        return $caseworkerModel;
    }

    /**
     * Get a caseworker using a request token - used by authentication
     *
     * @param string $token
     * @return CaseworkerModel
     */
    public function getByToken(string $token)
    {
        /** @var CaseworkerEntity $caseworker */
        $caseworker = $this->repository->findOneBy([
            'token' => $token,
        ]);

        return $this->translateToDataModel($caseworker);
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
