<?php

namespace App\Service;

use App\DataModel\Applications\Application;
use App\DataModel\Cases\Payment;
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
     * @param Rsa $bankCipher
     * @return CaseDataModel[]
     */
    public function getAllRefundable(Rsa $bankCipher)
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
                $application->getAccount()
                    ->setAccountNumber($accountDetails['account-number'])
                    ->setSortCode($accountDetails['sort-code']);
            }

            $case = new CaseDataModel($caseEntity->toArray(
                ['jsonData', 'assignedTo', 'poas', 'verification'],
                ['payment']
            ));
            $case->setApplication($application);
            $assignedTo = $caseEntity->getAssignedTo();
            if ($assignedTo !== null) {
                $case->setAssignedToId($assignedTo->getId());
            }

            //TODO: Remove once payment is populated
            $payment = new Payment();
            $payment->setAmount(10);
            $case->setPayment($payment);

            $cases[] = $case;
        }

        return $cases;
    }
}