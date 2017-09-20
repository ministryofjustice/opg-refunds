<?php

namespace App\Service;

use App\Entity\Cases\User as CaseworkerEntity;
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
}
