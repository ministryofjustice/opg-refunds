<?php

namespace App\Service;

use App\Entity\AbstractEntity;
use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use Opg\Refunds\Caseworker\DataModel\Cases\RefundCase as RefundCaseModel;
use App\Entity\Cases\Caseworker as CaseworkerEntity;
use App\Entity\Cases\RefundCase as RefundCaseEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Zend\Crypt\PublicKey\Rsa;

/**
 * Class Spreadsheet
 * @package App\Service
 */
class Spreadsheet
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
     * @param Rsa $bankCipher
     */
    public function __construct(EntityManager $entityManager, Rsa $bankCipher)
    {
        $this->repository = $entityManager->getRepository(RefundCaseEntity::class);
        $this->entityManager = $entityManager;
        $this->bankCipher = $bankCipher;
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
        //  Get the case using the trait method
        /** @var RefundCaseModel $refundCase */
        $refundCase = $this->traitTranslateToDataModel($entity);

        //  Deserialize the application from the JSON data
        /** @var RefundCaseEntity $entity */
        $applicationArray = json_decode($entity->getJsonData(), true);
        $accountDetails = json_decode($this->bankCipher->decrypt($applicationArray['account']['details']), true);

        //  Set the sort code and account numnber in the account
        $account = $refundCase->getApplication()
                              ->getAccount();
        $account->setAccountNumber($accountDetails['account-number'])
                ->setSortCode($accountDetails['sort-code']);

        //TODO: Remove once payment is populated
        $payment = new Payment();
        $payment->setAmount(10);
        $refundCase->setPayment($payment);

        return $refundCase;
    }
}