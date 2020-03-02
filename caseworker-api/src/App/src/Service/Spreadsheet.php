<?php

namespace App\Service;

use App\Service\Claim as ClaimService;
use DateInterval;
use DateTime;
use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Note as NoteModel;
use App\Entity\Cases\Claim as ClaimEntity;
use App\Entity\Cases\Payment as PaymentEntity;
use App\Entity\Cases\User as UserEntity;
use App\Service\Account as AccountService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;
use PDO;
use Aws\Kms\KmsClient;

use Opg\Refunds\Log\Initializer;

/**
 * Class Spreadsheet
 * @package App\Service
 */
class Spreadsheet implements Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;
    use EntityToModelTrait;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var EntityRepository
     */
    private $userRepository;

    /**
     * @var EntityRepository
     */
    private $paymentRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var KmsClient
     */
    private $kmsClient;

    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * @var array
     */
    private $spreadsheetConfig;

    /**
     * @var PDO
     */
    private $database;

    /**
     * Spreadsheet constructor
     *
     * @param EntityManager $entityManager
     * @param KmsClient $kmsClient
     * @param Rsa $bankCipher
     * @param Claim $claimService
     * @param AccountService $accountService
     * @param array $spreadsheetConfig
     * @param PDO $database
     */
    public function __construct(
        EntityManager $entityManager,
        KmsClient $kmsClient,
        ClaimService $claimService,
        AccountService $accountService,
        array $spreadsheetConfig,
        PDO $database
    ) {
        $this->repository = $entityManager->getRepository(ClaimEntity::class);
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
        $this->paymentRepository = $entityManager->getRepository(PaymentEntity::class);
        $this->entityManager = $entityManager;
        $this->kmsClient = $kmsClient;
        $this->claimService = $claimService;
        $this->accountService = $accountService;
        $this->spreadsheetConfig = $spreadsheetConfig;
        $this->database = $database;
    }

    /**
     * Get all refundable claims for a specific date. Using today will retrieve all newly accepted claims up to a
     * maximum of 3000
     *
     * @param DateTime $date
     * @param int $userId
     * @return ClaimModel[]
     */
    public function getAllRefundable(DateTime $date, int $userId)
    {
        $this->getLogger()->info('Getting all refundable claims for ' . date('c', $date->getTimestamp()) . ' user id ' . $userId);

        $start = microtime(true);

        $claimIds = $this->getSpreadsheetClaimIds($date);

        $this->getLogger()->debug(count($claimIds) . ' refundable claims retrieved in ' . $this->getElapsedTimeInMs($start) . 'ms');

        /** @var UserEntity $user */
        $user = $this->userRepository->findOneBy([
            'id' => $userId,
        ]);

        $addStart = microtime(true);

        $refundableClaims = [];
        $paymentParameters = [];
        $noteParameters = [];

        foreach ($claimIds as $claimId) {
            $start = microtime(true);

            $claim = $this->claimService->getClaimEntity($claimId['id']);

            $refundableClaims[] = $this->getRefundable($claim, $user, $paymentParameters, $noteParameters);

            $this->getLogger()->debug('Refundable claim with id ' . $claim->getId() . ' added in ' . $this->getElapsedTimeInMs($start) . 'ms. ' . count($refundableClaims) . ' in total');

            unset($claim);
        }

        unset($claimIds);

        $this->getLogger()->debug(count($refundableClaims) . ' refundable claims added in ' . $this->getElapsedTimeInMs($addStart) . 'ms');

        //Insert payments
        $paymentInsertCount = count($paymentParameters) / 4;
        if ($paymentInsertCount > 0) {
            $start = microtime(true);

            $paymentInsertSql = 'INSERT INTO payment (amount, method, added_datetime, claim_id) VALUES ';

            for ($i = 0; $i < $paymentInsertCount; $i++) {
                $paymentInsertSql .= ($i != 0) ? ', ' : '';
                $paymentInsertSql .= '(?, ?, ?, ?)';
            }

            $this->database->beginTransaction();
            $statement = $this->database->prepare($paymentInsertSql);
            $statement->execute($paymentParameters);
            $this->database->commit();

            $this->getLogger()->info($paymentInsertCount . ' payments inserted into the database in ' . $this->getElapsedTimeInMs($start) . 'ms');
        }

        //Insert notes
        $noteInsertCount = count($noteParameters) / 5;
        if ($noteInsertCount > 0) {
            $start = microtime(true);

            $noteInsertSql = 'INSERT INTO note (claim_id, user_id, created_datetime, type, message) VALUES ';

            for ($i = 0; $i < $noteInsertCount; $i++) {
                $noteInsertSql .= ($i != 0) ? ', ' : '';
                $noteInsertSql .= '(?, ?, ?, ?, ?)';
            }

            $this->database->beginTransaction();
            $statement = $this->database->prepare($noteInsertSql);
            $statement->execute($noteParameters);
            $this->database->commit();

            $this->getLogger()->info(($noteInsertCount) . ' notes inserted into the database in ' . $this->getElapsedTimeInMs($start) . 'ms');
        }

        $this->clearBankDetails();

        return $refundableClaims;
    }

    public function getAllHistoricRefundDates()
    {
        $start = microtime(true);

        $historicRefundDates = [];

        // Check for the presence of account details rather than the account hash because the account details are deleted after X days but the account hash remains.
        // Limiting to just those payments associated with claims that have account details prevents listing of historic refund dates that will result in empty spreadsheets
        $statement = $this->entityManager->getConnection()->executeQuery(
            'SELECT DISTINCT date_trunc(\'day\', p.added_datetime) AS historic_refund_date FROM payment p JOIN claim c ON p.claim_id = c.id WHERE p.added_datetime < CURRENT_DATE AND (c.json_data->\'account\'->\'details\') IS NOT NULL ORDER BY historic_refund_date DESC'
        );

        $results = $statement->fetchAll();

        foreach ($results as $result) {
            $historicRefundDate = new DateTime($result['historic_refund_date']);
            $historicRefundDates[] = date('Y-m-d', $historicRefundDate->getTimestamp());
        }

        $this->getLogger()->debug('Historic refund dates retrieved in ' . $this->getElapsedTimeInMs($start) . 'ms');

        //returns an array of historic refund dates
        return $historicRefundDates;
    }

    public function storeSpreadsheetHashes(array $spreadsheetHashes)
    {
        $start = microtime(true);

        $processed = 0;

        $this->database->beginTransaction();

        foreach ($spreadsheetHashes as $spreadsheetHash) {
            $claimCode = $spreadsheetHash['claimCode'];
            $claimId = IdentFormatter::parseId($claimCode);

            $statement = $this->database->prepare('UPDATE payment SET spreadsheet_hash = ? WHERE claim_id = ?');
            $statement->execute([$spreadsheetHash['hash'], $claimId]);

            $processed++;
        }

        $this->database->commit();

        $this->getLogger()->debug($processed . ' spreadsheet hashes updated in ' . $this->getElapsedTimeInMs($start) . 'ms');
    }

    public function validateSpreadsheetHashes(array $spreadsheetHashes, DateTime $date)
    {
        $added = [];
        $changed = [];
        $deleted = [];

        $processed = [];

        if (count($spreadsheetHashes) > 0) {
            $expectedClaimIds = $this->getSpreadsheetClaimIds($date);

            $rowIndex = 3;

            foreach ($expectedClaimIds as $expectedClaimId) {
                $expectedClaimCode = IdentFormatter::format($expectedClaimId['id']);

                $found = false;
                foreach ($spreadsheetHashes as $spreadsheetHash) {
                    if ($expectedClaimCode === $spreadsheetHash['claimCode']) {
                        $found = true;
                        break;
                    }
                }

                if ($found === false) {
                    $expectedClaim = $this->claimService->getClaimEntity($expectedClaimId['id']);

                    $deleted[$expectedClaimCode] = [
                        'claimCode' => $expectedClaimCode,
                        'row' => $rowIndex,
                        'hash' => $expectedClaim->getPayment()->getSpreadsheetHash(),
                    ];
                }

                $rowIndex++;
            }

            foreach ($spreadsheetHashes as $spreadsheetHash) {
                $claimCode = $spreadsheetHash['claimCode'];
                $claimId = IdentFormatter::parseId($claimCode);

                if ($claimId === false) {
                    $deleted[$claimCode] = $spreadsheetHash;
                } else {
                    $claimEntity = $this->claimService->getClaimEntity($claimId);

                    if ($claimEntity === null) {
                        $added[$claimCode] = $spreadsheetHash;
                    } elseif ($claimEntity->getPayment()->getSpreadsheetHash() !== $spreadsheetHash['hash']) {
                        if (isset($changed[$claimCode])) {
                            $added[] = $spreadsheetHash;
                        } else {
                            $changed[$claimCode] = $spreadsheetHash;
                        }
                    } elseif (isset($processed[$claimCode])) {
                        $added[] = $spreadsheetHash;
                    }

                    $processed[$claimCode] = $spreadsheetHash;
                }
            }
        }

        return [
            'valid' => count($added) === 0 && count($changed) === 0 && count($deleted) === 0,
            'added' => $added,
            'changed' => $changed,
            'deleted' => $deleted
        ];
    }

    /**
     * @param DateTime $date
     * @return array
     */
    private function getSpreadsheetClaimIds(DateTime $date): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('c');

        if ($date == new DateTime('today')) {
            // Creating today's spreadsheet which contains all of yesterday's approved claims
            $queryBuilder->leftJoin('c.payment', 'p')
                ->select('c.id')
                ->where('c.status = :status AND (p.addedDateTime IS NULL OR p.addedDateTime >= :today) AND c.finishedDateTime < :today')
                ->orderBy('c.finishedDateTime', 'ASC')
                ->setMaxResults(3000)
                ->setParameters(['status' => ClaimModel::STATUS_ACCEPTED, 'today' => $date]);
        } else {
            $startDateTime = clone $date;
            $endDateTime = $date->add(new DateInterval('P1D'));
            $queryBuilder->join('c.payment', 'p')
                ->select('c.id')
                ->where('c.status = :status AND p.addedDateTime >= :startDateTime AND p.addedDateTime < :endDateTime')
                ->orderBy('c.finishedDateTime', 'ASC')
                ->setParameters([
                    'status' => ClaimModel::STATUS_ACCEPTED,
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime
                ]);
        }

        $claimIds = $queryBuilder->getQuery()->getScalarResult();

        return $claimIds;
    }

    /**
     * @param ClaimEntity $entity
     * @param UserEntity $user
     * @param array $paymentParameters
     * @param array $noteParameters
     * @return ClaimModel
     */
    private function getRefundable(ClaimEntity $entity, UserEntity $user, array & $paymentParameters, array & $noteParameters)
    {
        $claimId = $entity->getId();
        $userId = $user->getId();

        $this->getLogger()->info("Getting and decrypting refundable claim with id {$claimId} user id {$userId}");

        $start = microtime(true);

        //Create and populate datamodel manually to be as efficient as possible
        $claim = new ClaimModel();
        $claim->setId($entity->getId());
        $applicationArray = $entity->getJsonData();
        $claim->setApplication(new Application($entity->getJsonData()));
        if ($entity->getAccountHash() !== null) {
            $claim->setAccountHash($entity->getAccountHash());
        }
        $claim->setFinishedByName($entity->getFinishedBy()->getName());

        if ($this->accountService->isBuildingSociety($claim->getAccountHash()) === true) {
            $claim->getApplication()->getAccount()->setBuildingSociety(true);
            $claim->getApplication()->getAccount()->setInstitutionName(
                $this->accountService->getBuildingSocietyName($claim->getAccountHash())
            );
        }

        $this->getLogger()->debug('Refundable claim with id ' . $claim->getId() . ' translated to datamodel in ' . $this->getElapsedTimeInMs($start) . 'ms');
        $start = microtime(true);

        /** @var ClaimEntity $entity */
        if ($entity->getPayment() === null) {
            $refundAmount = RefundCalculator::getRefundTotalAmount($entity, time());
            $refundAmountString = money_format('£%i', $refundAmount);

            //Create payment entity
            $payment = new PaymentEntity($refundAmount, $claim->getApplication()->isRefundByCheque() ? 'Cheque' : 'Bank transfer', $entity);
            $claim->setPayment($payment->getAsDataModel());

            //Set payment parameters
            $paymentParameters[] = $payment->getAmount();
            $paymentParameters[] = $payment->getMethod();
            $paymentParameters[] = date('c', $payment->getAddedDateTime()->getTimestamp());
            $paymentParameters[] = $payment->getClaim()->getId();

            $message = "A refund amount of $refundAmountString was added to the claim";
            $this->getLogger()->info($message . ' by ' . $user->getId() . ' ' . $user->getName());

            //Set note parameters
            $noteParameters[] = $claimId;
            $noteParameters[] = $userId;
            $noteParameters[] = date('c');
            $noteParameters[] = NoteModel::TYPE_REFUND_ADDED;
            $noteParameters[] = $message;

            $this->getLogger()->debug('Payment for refundable claim with id ' . $claim->getId() . ' calculated in ' . $this->getElapsedTimeInMs($start) . 'ms');
            $start = microtime(true);
        } else {
            $payment = $entity->getPayment();
            $refundAmount = $payment->getAmount();
            $refundAmountString = money_format('£%i', $refundAmount);
            $claim->setPayment($payment->getAsDataModel());
        }

        $message = "A refundable claim for $refundAmountString was downloaded";
        $this->getLogger()->info($message . ' by ' . $user->getId() . ' ' . $user->getName());

        //Set note parameters
        $noteParameters[] = $claimId;
        $noteParameters[] = $userId;
        $noteParameters[] = date('c');
        $noteParameters[] = NoteModel::TYPE_REFUND_DOWNLOADED;
        $noteParameters[] = $message;

        if (!$claim->getApplication()->isRefundByCheque()) {
            if (array_key_exists('details', $applicationArray['account'])
                && is_string($applicationArray['account']['details'])) {
                //  Deserialize the application from the JSON data
                $accountDetails = json_decode($this->decryptBankDetails($applicationArray['account']['details']), true);

                //  Set the sort code and account number in the account
                $account = $claim->getApplication()->getAccount();
                $account->setAccountNumber($accountDetails['account-number'])->setSortCode($accountDetails['sort-code']);
            } else {
                $claim->getApplication()->setRefundByCheque(true);
            }
        }

        $this->getLogger()->debug('Bank details decrypted for refundable claim with id ' . $claim->getId() . ' in ' . $this->getElapsedTimeInMs($start) . 'ms');

        return $claim;
    }

    private function clearBankDetails()
    {
        //Gets an array of historic refund dates
        $historicRefundDates = $this->getAllHistoricRefundDates();

        $this->getLogger()->debug('1. Historic Refund Dates are :' . print_r($this->$historicRefundDates));


        $deleteAfterHistoricalRefundDates = $this->spreadsheetConfig['delete_after_historical_refund_dates'];
        $this->getLogger()->debug('2. The deleteAfterHistoricalRefundDates variable is :' . $this->$deleteAfterHistoricalRefundDates);

        if (count($historicRefundDates) >= $deleteAfterHistoricalRefundDates) {
            $start = microtime(true);

            $deleteAfterHistoricalRefundDate = new DateTime($historicRefundDates[$deleteAfterHistoricalRefundDates - 1]);
            $this->getLogger()->debug('3. The deleteAfterHistoricalRefundDate variable is :' . $this->$deleteAfterHistoricalRefundDate);

            $statement = $this->entityManager->getConnection()->executeQuery(
                'UPDATE claim SET json_data = json_data #- \'{account,details}\' WHERE id IN (SELECT c.id FROM claim c LEFT OUTER JOIN payment p ON c.id = p.claim_id WHERE (c.json_data->\'account\'->\'details\') IS NOT NULL AND ((status = \'rejected\' AND finished_datetime < :date) OR p.added_datetime < :date))',
                ['date' => $deleteAfterHistoricalRefundDate->format('Y-m-d')]
            );

            $result = $statement->fetchAll();
            $updateCount = count($result);

            $this->getLogger()->debug('Bank details deleted in ' . $this->getElapsedTimeInMs($start) . 'ms');

            if ($updateCount > 0) {
                $this->getLogger()->notice("Bank details for $updateCount claim(s) were deleted");
            }

            $start = microtime(true);

            $this->entityManager->flush();
            $this->entityManager->clear();

            $this->getLogger()->debug('Bank detail deletion changes flushed to the database in ' . $this->getElapsedTimeInMs($start) . 'ms');
        }
    }

    /**
     * Decrypts bank details via AWS KMS.
     *
     * @param $ciphertext
     * @return string
     */
    private function decryptBankDetails($ciphertext)
    {
        $clearText = $this->kmsClient->decrypt([
            'CiphertextBlob' => base64_decode($ciphertext)
        ]);

        return $clearText->get('Plaintext');
    }

    /**
     * @param $start
     * @return float
     */
    private function getElapsedTimeInMs($start): float
    {
        return round((microtime(true) - $start) * 1000);
    }
}
