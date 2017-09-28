<?php

namespace App\Service;

use App\Entity\AbstractEntity;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use App\Entity\Cases\Claim as ClaimEntity;
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
     * Spreadsheet constructor
     *
     * @param EntityManager $entityManager
     * @param Rsa $bankCipher
     */
    public function __construct(EntityManager $entityManager, Rsa $bankCipher)
    {
        $this->repository = $entityManager->getRepository(ClaimEntity::class);
        $this->entityManager = $entityManager;
        $this->bankCipher = $bankCipher;
    }

    /**
     * Get all refundable claims
     *
     * @return ClaimModel[]
     */
    public function getAllRefundable()
    {
        $claims = $this->repository->findBy(['status' => ClaimModel::STATUS_ACCEPTED]);

        return $this->translateToDataModelArray($claims);
    }

    /**
     * @param AbstractEntity $entity
     * @return \Opg\Refunds\Caseworker\DataModel\AbstractDataModel
     */
    public function translateToDataModel($entity)
    {
        //  Get the claim using the trait method
        /** @var ClaimModel $claim */
        $claim = $this->traitTranslateToDataModel($entity);

        //  Deserialize the application from the JSON data
        /** @var ClaimEntity $entity */
        $applicationArray = json_decode($entity->getJsonData(), true);
        $accountDetails = json_decode($this->bankCipher->decrypt($applicationArray['account']['details']), true);

        //  Set the sort code and account numnber in the account
        $account = $claim->getApplication()
                              ->getAccount();
        $account->setAccountNumber($accountDetails['account-number'])
                ->setSortCode($accountDetails['sort-code']);

        //TODO: Remove once payment is populated
        $payment = new Payment();
        $payment->setAmount(10);
        $claim->setPayment($payment);

        return $claim;
    }
}