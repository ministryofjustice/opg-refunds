<?php

namespace App\Service;

use App\Entity\AbstractEntity;
use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\Cases\Caseworker;
use Opg\Refunds\Caseworker\DataModel\Cases\RefundCase as RefundCaseModel;
use App\Entity\Cases\RefundCase as RefundCaseEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class RefundCase
 * @package App\Service
 */
class RefundCase
{
    use EntityToModelTrait {
        translateToDataModel as protected traitTranslateToDataModel;
    }

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

    /**
     * @param AbstractEntity $entity
     * @return \Opg\Refunds\Caseworker\DataModel\AbstractDataModel
     */
    public function translateToDataModel($entity)
    {
        //  Get the case using the trait method
        /** @var RefundCaseModel $refundCase */
        $refundCase = $this->traitTranslateToDataModel($entity);

        //  Deserialize the application from the JSON data
        /** @var RefundCaseEntity $entity */
        $application = new Application($entity->getJsonData());
        $refundCase->setApplication($application);

        $assignedTo = $entity->getAssignedTo();
        if ($assignedTo instanceof Caseworker) {
            $refundCase->setAssignedToId($assignedTo->getId());
        }

        return $refundCase;
    }
}
