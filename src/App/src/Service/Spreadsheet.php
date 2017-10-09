<?php

namespace App\Service;

use App\Service\Claim as ClaimService;
use DateInterval;
use DateTime;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
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
     * @var Rsa
     */
    private $bankCipher;

    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * Spreadsheet constructor
     *
     * @param EntityManager $entityManager
     * @param Rsa $bankCipher
     */
    public function __construct(EntityManager $entityManager, Rsa $bankCipher, ClaimService $claimService)
    {
        $this->repository = $entityManager->getRepository(ClaimEntity::class);
        $this->entityManager = $entityManager;
        $this->bankCipher = $bankCipher;
        $this->claimService = $claimService;
    }

    /**
     * Get all refundable claims for a specific date. Using today will retrieve all newly accepted claims up to a
     * maximum of 3000
     *
     * @param DateTime $date
     * @return ClaimModel[]
     */
    public function getAllRefundable(DateTime $date, int $userId)
    {
        $queryBuilder = $this->repository->createQueryBuilder('c');

        if ($date == new DateTime('today')) {
            // Creating today's spreadsheet
            $queryBuilder->leftJoin('c.payment', 'p')
                ->where('c.status = :status AND (p.addedDateTime IS NULL OR p.addedDateTime >= :today)')
                ->orderBy('c.finishedDateTime', 'ASC')
                ->setMaxResults(3000)
                ->setParameters(['status' => ClaimModel::STATUS_ACCEPTED, 'today' => $date]);
        } else {
            // Retrieving a previous spreadsheet
            $startDateTime = clone $date;
            $endDateTime = $date->add(new DateInterval('P1D'));
            $queryBuilder->join('c.payment', 'p')
                ->where('c.status = :status AND p.addedDateTime >= :startDateTime AND p.addedDateTime < :endDateTime')
                ->orderBy('c.finishedDateTime', 'ASC')
                ->setParameters([
                    'status' => ClaimModel::STATUS_ACCEPTED,
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime
                ]);
        }

        $claims = $queryBuilder->getQuery()->getResult();

        $refundableClaims = [];

        foreach ($claims as $claim) {
            $refundableClaims[] = $this->getRefundable($claim, $userId);
        }

        return $refundableClaims;
    }

    public function getAllHistoricRefundDates()
    {
        $historicRefundDates = [];

        $statement = $this->entityManager->getConnection()->executeQuery(
            'SELECT DISTINCT date_trunc(\'day\', added_datetime) AS historic_refund_date FROM payment WHERE added_datetime < CURRENT_DATE'
        );

        $results = $statement->fetchAll();

        foreach ($results as $result) {
            $historicRefundDate = new DateTime($result['historic_refund_date']);
            $historicRefundDates[] = date('Y-m-d', $historicRefundDate->getTimestamp());
        }

        return $historicRefundDates;
    }

    /**
     * @param ClaimEntity $entity
     * @param int $userId
     * @return ClaimModel
     */
    private function getRefundable(ClaimEntity $entity, int $userId)
    {
        $claimId = $entity->getId();

        //  Get the claim using the trait method
        /** @var ClaimModel $claim */
        $claim = $this->translateToDataModel($entity);

        $refundAmount = $this->getRefundTotalAmount($claim);
        $refundAmountString = money_format('£%i', $refundAmount);

        /** @var ClaimEntity $entity */
        if ($entity->getPayment() === null) {
            //Create and persist payment
            $payment = new PaymentEntity($refundAmount, 'Bank transfer');
            $this->entityManager->persist($payment);
            $entity->setPayment($payment);

            $message = "A refund amount of $refundAmountString was added to the claim";
            $this->claimService->addNote($claimId, $userId, 'Refund added', $message);
        } elseif (abs(($entity->getPayment()->getAmount()-$refundAmount)/$refundAmount) > 0.00001) {
            $originalPaymentAmount = $entity->getPayment()->getAmount();

            //Update amount in case interest has changed
            $entity->getPayment()->setAmount($refundAmount);

            $newRefundAmountString = money_format('£%i', $originalPaymentAmount);
            $message = "The refund amount for claim was changed from $refundAmountString to $newRefundAmountString";
            $this->claimService->addNote($claimId, $userId, 'Refund updated', $message);
        }

        $message = "A refundable claim for $refundAmountString was downloaded";
        $this->claimService->addNote($claimId, $userId, 'Refund downloaded', $message);

        //  Retrieve updated claim
        $entity = $this->repository->findOneBy([
            'id' => $claimId,
        ]);

        //  Get the claim using the trait method
        /** @var ClaimModel $claim */
        $claim = $this->translateToDataModel($entity);

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