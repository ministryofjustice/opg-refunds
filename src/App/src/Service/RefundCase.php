<?php

namespace App\Service;

use Opg\Refunds\Caseworker\DataModel\Cases\RefundCase as RefundCaseModel;
use App\Entity\Cases\Claim as RefundCaseEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class RefundCase
 * @package App\Service
 */
class RefundCase
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
     * Case constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(RefundCaseEntity::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Get all refund cases
     *
     * @return RefundCaseModel[]
     */
    public function getAll()
    {
        /** @var RefundCaseEntity[] $refundCases */
        $refundCases = $this->repository->findBy([]);

        return $this->translateToDataModelArray($refundCases);
    }
}
