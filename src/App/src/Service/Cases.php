<?php

namespace App\Service;

use App\Entity\Cases\RefundCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class Cases
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
     * Cases constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(RefundCase::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @return array
     */
    public function getAllAsArray()
    {
        $caseArrays = [];

        $cases = $this->repository->findBy([], null);
        foreach ($cases as $case) {
            /** @var RefundCase $case */
            $caseArrays[] = $case->toArray();
        }

        return $caseArrays;
    }
}