<?php

namespace App\Service;

use App\DataModel\Applications\Application;
use App\DataModel\Cases\RefundCase as CaseDataModel;
use App\Entity\Cases\RefundCase as CaseEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Zend\Crypt\PublicKey\Rsa;

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
        $this->repository = $entityManager->getRepository(CaseEntity::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @return CaseEntity[]
     */
    public function getAllEntities()
    {
        $cases = $this->repository->findBy([], null);

        return $cases;
    }

    /**
     * @return array
     */
    public function getAllEntitiesAsArray()
    {
        $caseArrays = [];

        $cases = $this->getAllEntities();
        foreach ($cases as $case) {
            /** @var CaseEntity $case */
            $caseArrays[] = $case->toArray();
        }

        return $caseArrays;
    }

    /**
     * @return CaseDataModel[]
     */
    public function getAllDataModels()
    {
        return $this->getAllDataModelsWithBankDetails(null);
    }

    /**
     * @param Rsa $bankCipher
     * @return CaseDataModel[]
     */
    public function getAllDataModelsWithBankDetails(Rsa $bankCipher)
    {
        $cases = [];

        $caseEntities = $this->getAllEntities();
        foreach ($caseEntities as $caseEntity) {
            /** @var CaseEntity $caseEntity */
            $applicationJsonData = $caseEntity->getJsonData();
            $application = new Application($applicationJsonData);

            if ($bankCipher !== null) {
                $applicationArray = json_decode($applicationJsonData, true);
                $accountDetails = json_decode($bankCipher->decrypt($applicationArray['account']['details']), true);
                $application->getAccount()->setAccountNumber($accountDetails['account-number']);
                $application->getAccount()->setSortCode($accountDetails['sort-code']);
            }

            $case = new CaseDataModel($caseEntity->toArray(['jsonData', 'assignedTo', 'poas', 'verification'], []));
            $case->setApplication($application);
            $case->setAssignedToId($caseEntity->getAssignedTo()->getId());

            $cases[] = $case;
        }

        return $cases;
    }
}