<?php

namespace App\Service;

use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use App\Entity\Cases\Claim as ClaimEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class Claim
 * @package App\Service
 */
class Claim
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
     * Claim constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(ClaimEntity::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Get all claims
     *
     * @return ClaimModel[]
     */
    public function getAll()
    {
        /** @var ClaimEntity[] $claims */
        $claims = $this->repository->findBy([]);

        return $this->translateToDataModelArray($claims);
    }
}
