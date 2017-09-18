<?php

namespace App\Service;

use App\Entity\AbstractEntity;
use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\Cases\Caseworker;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use Opg\Refunds\Caseworker\DataModel\Cases\RefundCase as RefundCaseModel;
use App\Entity\Cases\RefundCase as RefundCaseEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Zend\Crypt\PublicKey\Rsa;

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
     * @var Rsa
     */
    private $bankCipher;

    /**
     * Case constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, Rsa $bankCipher)
    {
        $this->repository = $entityManager->getRepository(RefundCaseEntity::class);
        $this->entityManager = $entityManager;
        $this->bankCipher = $bankCipher;
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
     * Get all refundable cases
     *
     * @return RefundCaseModel[]
     */
    public function getAllRefundable()
    {
        //  TODO: Return only those which can be refunded
        $refundCases = $this->repository->findBy([]);

        return $this->translateToDataModelArray($refundCases);
    }

    /**
     * @param AbstractEntity $entity
     * @return \Opg\Refunds\Caseworker\DataModel\AbstractDataModel
     */
    public function translateToDataModel($entity)
    {
        /** @var RefundCaseEntity $entity */
        $applicationJsonData = $entity->getJsonData();
        $application = new Application($applicationJsonData);

        if ($this->bankCipher !== null) {
            $applicationArray = json_decode($applicationJsonData, true);
            $accountDetails = json_decode($this->bankCipher->decrypt($applicationArray['account']['details']), true);

            $application->getAccount()
                ->setAccountNumber($accountDetails['account-number'])
                ->setSortCode($accountDetails['sort-code']);
        }

        //  Get the case using the trait method
        /** @var RefundCaseModel $refundCase */
        $refundCase = $this->traitTranslateToDataModel($entity);

        $refundCase->setApplication($application);
        $assignedTo = $entity->getAssignedTo();

        if ($assignedTo instanceof Caseworker) {
            $refundCase->setAssignedToId($assignedTo->getId());
        }

        //TODO: Remove once payment is populated
        $payment = new Payment();
        $payment->setAmount(10);
        $refundCase->setPayment($payment);

        return $refundCase;
    }
}
