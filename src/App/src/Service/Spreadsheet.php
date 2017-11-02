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
     * @var array
     */
    private $spreadsheetConfig;

    /**
     * Spreadsheet constructor
     *
     * @param EntityManager $entityManager
     * @param Rsa $bankCipher
     * @param Claim $claimService
     * @param array $spreadsheetConfig
     */
    public function __construct(EntityManager $entityManager, Rsa $bankCipher, ClaimService $claimService, array $spreadsheetConfig)
    {
        $this->repository = $entityManager->getRepository(ClaimEntity::class);
        $this->entityManager = $entityManager;
        $this->bankCipher = $bankCipher;
        $this->claimService = $claimService;
        $this->spreadsheetConfig = $spreadsheetConfig;
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
            // Creating today's spreadsheet which contains all of yesterday's approved claims
            $queryBuilder->leftJoin('c.payment', 'p')
                ->where('c.status = :status AND (p.addedDateTime IS NULL OR p.addedDateTime >= :today) AND c.finishedDateTime < :today')
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

        $this->clearBlankDetails();

        return $refundableClaims;
    }

    public function getAllHistoricRefundDates()
    {
        $historicRefundDates = [];

        $maxHistoricalRefundDates = $this->spreadsheetConfig['max_historical_refund_dates'];

        $statement = $this->entityManager->getConnection()->executeQuery(
            'SELECT DISTINCT date_trunc(\'day\', added_datetime) AS historic_refund_date FROM payment WHERE added_datetime < CURRENT_DATE ORDER BY historic_refund_date DESC LIMIT ?',
            [$maxHistoricalRefundDates]
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

        $refundAmount = RefundCalculator::getRefundTotalAmount($claim, time());
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

    private function clearBlankDetails()
    {

    }
}
