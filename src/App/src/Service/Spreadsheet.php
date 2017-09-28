<?php

namespace App\Service;

use App\Entity\AbstractEntity;
use DateTime;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment as PaymentModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use App\Entity\Cases\Claim as ClaimEntity;
use App\Entity\Cases\Payment as PaymentEntity;
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
        $claims = $this->repository->findBy([
            'status' => ClaimModel::STATUS_ACCEPTED
        ]);

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

        $refundAmount = $this->getRefundTotalAmount($claim);

        /** @var ClaimEntity $entity */
        if ($entity->getPayment() === null) {
            //Create and persist payment
            $payment = new PaymentEntity($refundAmount, 'Bank transfer', $entity);
            $this->entityManager->persist($payment);
        } else {
            //Update amount in case interest has changed
            $entity->getPayment()->setAmount($refundAmount);
        }

        $this->entityManager->flush();

        //  Retrieve updated claim
        $entity = $this->repository->findOneBy([
            'id' => $entity->getId(),
        ]);

        //  Get the claim using the trait method
        /** @var ClaimModel $claim */
        $claim = $this->traitTranslateToDataModel($entity);

        //  Deserialize the application from the JSON data
        $applicationArray = json_decode($entity->getJsonData(), true);
        $accountDetails = json_decode($this->bankCipher->decrypt($applicationArray['account']['details']), true);

        //  Set the sort code and account number in the account
        $account = $claim->getApplication()
                         ->getAccount();
        $account->setAccountNumber($accountDetails['account-number'])
                ->setSortCode($accountDetails['sort-code']);

        return $claim;
    }

    private function getRefundAmount(PoaModel $poa)
    {
        //TODO: Use Neil's calculations
        if ($poa->getOriginalPaymentAmount() === 'noRefund') {
            return 0.0;
        }

        $upperRefundAmount = $poa->getOriginalPaymentAmount() === 'orMore';

        if ($poa->getReceivedDate() >= new DateTime('2013-04-01') && $poa->getReceivedDate() < new DateTime('2013-10-01')) {
            return $upperRefundAmount ? 54.0 : 27.0;
        } elseif ($poa->getReceivedDate() >= new DateTime('2013-10-01') && $poa->getReceivedDate() < new DateTime('2014-04-01')) {
            return $upperRefundAmount ? 34.0 : 17.0;
        } elseif ($poa->getReceivedDate() >= new DateTime('2014-04-01') && $poa->getReceivedDate() < new DateTime('2015-04-01')) {
            return $upperRefundAmount ? 37.0 : 18.0;
        } elseif ($poa->getReceivedDate() >= new DateTime('2015-04-01') && $poa->getReceivedDate() < new DateTime('2016-04-01')) {
            return $upperRefundAmount ? 38.0 : 19.0;
        } elseif ($poa->getReceivedDate() >= new DateTime('2016-04-01') && $poa->getReceivedDate() < new DateTime('2017-04-01')) {
            return $upperRefundAmount ? 45.0 : 22.0;
        }

        return 0.0;
    }

    /**
     * @param PoaModel $poa
     * @param float $refundAmount
     * @return float
     */
    private function getAmountWithInterest(PoaModel $poa, $refundAmount): float
    {
        //TODO: Use Neil's calculations
        $now = time();
        $diff = $now - $poa->getReceivedDate()->getTimestamp();
        $diffInYears = $diff / 31536000;

        $interestRate = 0.5;

        $refundAmountWithInterest = round($refundAmount * pow(1 + ($interestRate / 100), $diffInYears), 2);

        return $refundAmountWithInterest;
    }

    /**
     * @param ClaimModel $claim
     * @return float
     */
    private function getRefundTotalAmount(ClaimModel $claim): float
    {
        $refundTotalAmount = 0.0;
        foreach ($claim->getPoas() as $poa) {
            $refundAmount = $this->getRefundAmount($poa);
            $refundTotalAmount += $this->getAmountWithInterest($poa, $refundAmount);
        }
        return $refundTotalAmount;
    }
}