<?php

namespace App\Service;

use App\Service\Claim as ClaimService;
use DateInterval;
use DateTime;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Note as NoteModel;
use App\Entity\Cases\Claim as ClaimEntity;
use App\Entity\Cases\Note as NoteEntity;
use App\Entity\Cases\Payment as PaymentEntity;
use App\Entity\Cases\User as UserEntity;
use App\Service\Account as AccountService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;
use Zend\Crypt\PublicKey\Rsa;
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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Rsa
     */
    private $bankCipher;

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
     * Spreadsheet constructor
     *
     * @param EntityManager $entityManager
     * @param KmsClient $kmsClient
     * @param Rsa $bankCipher
     * @param Claim $claimService
     * @param AccountService $accountService
     * @param array $spreadsheetConfig
     */
    public function __construct(EntityManager $entityManager, KmsClient $kmsClient, Rsa $bankCipher, ClaimService $claimService, AccountService $accountService, array $spreadsheetConfig)
    {
        $this->repository = $entityManager->getRepository(ClaimEntity::class);
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
        $this->entityManager = $entityManager;
        $this->kmsClient = $kmsClient;
        $this->bankCipher = $bankCipher;
        $this->claimService = $claimService;
        $this->accountService = $accountService;
        $this->spreadsheetConfig = $spreadsheetConfig;
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

        $claims = $this->getSpreadsheetClaims($date);

        $this->getLogger()->debug(count($claims) . ' refundable claims retrieved in ' . $this->getElapsedTimeInMs($start) . 'ms');

        $refundableClaims = [];

        /** @var UserEntity $user */
        $user = $this->userRepository->findOneBy([
            'id' => $userId,
        ]);

        $addStart = microtime(true);

        foreach ($claims as $claim) {
            $start = microtime(true);

            $refundableClaims[] = $this->getRefundable($claim, $user);

            $this->getLogger()->debug('Refundable claim with id ' . $claim->getId() . ' added in ' . $this->getElapsedTimeInMs($start) . 'ms');
        }

        $this->getLogger()->debug('All refundable claims ' . $claim->getId() . ' added in ' . $this->getElapsedTimeInMs($addStart) . 'ms');

        $start = microtime(true);

        $this->entityManager->flush();

        $this->getLogger()->debug('Changes flushed to database in ' . $this->getElapsedTimeInMs($start) . 'ms');

        //$this->clearBankDetails();

        return $refundableClaims;
    }

    public function getAllHistoricRefundDates()
    {
        $historicRefundDates = [];

        // Check for the presence of account details rather than the account hash because the account details are deleted after X days but the account hash remains.
        // Limiting to just those payments associated with claims that have account details prevents listing of historic refund dates that will result in empty spreadsheets
        $statement = $this->entityManager->getConnection()->executeQuery(
            'SELECT DISTINCT date_trunc(\'day\', p.added_datetime) AS historic_refund_date FROM claim c JOIN payment p ON c.payment_id = p.id WHERE p.added_datetime < CURRENT_DATE AND (c.json_data->\'account\'->\'details\') IS NOT NULL ORDER BY historic_refund_date DESC'
        );

        $results = $statement->fetchAll();

        foreach ($results as $result) {
            $historicRefundDate = new DateTime($result['historic_refund_date']);
            $historicRefundDates[] = date('Y-m-d', $historicRefundDate->getTimestamp());
        }

        return $historicRefundDates;
    }

    public function storeSpreadsheetHashes(array $spreadsheetHashes)
    {
        foreach ($spreadsheetHashes as $spreadsheetHash) {
            $claimCode = $spreadsheetHash['claimCode'];
            $claimId = IdentFormatter::parseId($claimCode);

            $claimEntity = $this->claimService->getClaimEntity($claimId);

            $claimEntity->getPayment()->setSpreadsheetHash($spreadsheetHash['hash']);
        }

        $this->entityManager->flush();
    }

    public function validateSpreadsheetHashes(array $spreadsheetHashes, DateTime $date)
    {
        $added = [];
        $changed = [];
        $deleted = [];

        $processed = [];

        if (count($spreadsheetHashes) > 0) {
            $expectedClaims = $this->getSpreadsheetClaims($date);

            $rowIndex = 3;

            foreach ($expectedClaims as $expectedClaim) {
                $expectedClaimCode = IdentFormatter::format($expectedClaim->getId());

                $found = false;
                foreach ($spreadsheetHashes as $spreadsheetHash) {
                    if ($expectedClaimCode === $spreadsheetHash['claimCode']) {
                        $found = true;
                        break;
                    }
                }

                if ($found === false) {
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
     * @return ClaimEntity[]
     */
    private function getSpreadsheetClaims(DateTime $date): array
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
        return $claims;
    }

    /**
     * @param ClaimEntity $entity
     * @param UserEntity $user
     * @return ClaimModel
     */
    private function getRefundable(ClaimEntity $entity, UserEntity $user)
    {
        $claimId = $entity->getId();
        $userId = $user->getId();

        $this->getLogger()->info("Getting and decrypting refundable claim with id {$claimId} user id {$userId}");

        $start = microtime(true);

        //  Get the claim using the trait method
        /** @var ClaimModel $claim */
        $claim = $this->translateToDataModel($entity);

        $this->getLogger()->debug('Refundable claim with id ' . $claim->getId() . ' translated to datamodel in ' . $this->getElapsedTimeInMs($start) . 'ms');
        $start = microtime(true);

        /** @var ClaimEntity $entity */
        if ($entity->getPayment() === null) {
            $refundAmount = RefundCalculator::getRefundTotalAmount($claim, time());
            $refundAmountString = money_format('£%i', $refundAmount);

            //Create and persist payment
            $payment = new PaymentEntity($refundAmount, $claim->getApplication()->isRefundByCheque() ? 'Cheque' : 'Bank transfer');
            $this->entityManager->persist($payment);
            $entity->setPayment($payment);

            $message = "A refund amount of $refundAmountString was added to the claim";
            //$note = new NoteEntity(NoteModel::TYPE_REFUND_ADDED, $message, $entity, $user);
            //$this->entityManager->persist($note);
            $this->getLogger()->info($message . ' by ' . $user->getId() . ' ' . $user->getName());
        } else {
            $refundAmount = $entity->getPayment()->getAmount();
            $refundAmountString = money_format('£%i', $refundAmount);
        }

        $this->getLogger()->debug('Payment for refundable claim with id ' . $claim->getId() . ' calculated in ' . $this->getElapsedTimeInMs($start) . 'ms');
        $start = microtime(true);

        $message = "A refundable claim for $refundAmountString was downloaded";
        //$note = new NoteEntity(NoteModel::TYPE_REFUND_DOWNLOADED, $message, $entity, $user);
        //$this->entityManager->persist($note);
        $this->getLogger()->info($message . ' by ' . $user->getId() . ' ' . $user->getName());

        $this->getLogger()->debug('Downloaded note for refundable claim with id ' . $claim->getId() . ' added in ' . $this->getElapsedTimeInMs($start) . 'ms');
        $start = microtime(true);

        //  Retrieve updated claim
        $entity = $this->repository->findOneBy([
            'id' => $claimId,
        ]);

        //  Get the claim using the trait method
        /** @var ClaimModel $claim */
        $claim = $this->translateToDataModel($entity);

        if ($this->accountService->isBuildingSociety($claim->getAccountHash()) === true) {
            $claim->getApplication()->getAccount()->setBuildingSociety(true);
            $claim->getApplication()->getAccount()->setInstitutionName(
                $this->accountService->getBuildingSocietyName($claim->getAccountHash())
            );
        }

        $this->getLogger()->debug('Refundable claim with id ' . $claim->getId() . ' retrieved and translated to datamodel in ' . $this->getElapsedTimeInMs($start) . 'ms');
        $start = microtime(true);

        if (!$claim->getApplication()->isRefundByCheque()) {
            //  Deserialize the application from the JSON data
            $applicationArray = $entity->getJsonData();
            $accountDetails = json_decode($this->decryptBankDetails($applicationArray['account']['details']), true);

            //  Set the sort code and account number in the account
            $account = $claim->getApplication()
                ->getAccount();
            $account->setAccountNumber($accountDetails['account-number'])
                ->setSortCode($accountDetails['sort-code']);
        }

        $this->getLogger()->debug('Bank details decrypted for refundable claim with id ' . $claim->getId() . ' in ' . $this->getElapsedTimeInMs($start) . 'ms');

        return $claim;
    }

    private function clearBankDetails()
    {
        $historicRefundDates = $this->getAllHistoricRefundDates();

        $deleteAfterHistoricalRefundDates = $this->spreadsheetConfig['delete_after_historical_refund_dates'];
        if (count($historicRefundDates) >= $deleteAfterHistoricalRefundDates) {
            $deleteAfterHistoricalRefundDate = new DateTime($historicRefundDates[$deleteAfterHistoricalRefundDates - 1]);

            $statement = $this->entityManager->getConnection()->executeQuery(
                'UPDATE claim SET json_data = (json_data #- \'{account,details}\')::json WHERE id IN (SELECT c.id FROM claim c LEFT OUTER JOIN payment p ON c.payment_id = p.id WHERE (c.json_data->\'account\'->\'details\') IS NOT NULL AND ((status = \'rejected\' AND finished_datetime < :date) OR p.added_datetime < :date))',
                ['date' => $deleteAfterHistoricalRefundDate->format('Y-m-d')]
            );

            $result = $statement->fetchAll();
            $updateCount = count($result);

            if ($updateCount > 0) {
                $this->getLogger()->notice("Bank details for $updateCount claim(s) were deleted");
            }
        }
    }

    /**
     * Decrypts bank details. First it tries AWS KMS.
     * If that fails it falls back to the original public/private key.
     *
     * @param $ciphertext
     * @return string
     */
    private function decryptBankDetails($ciphertext)
    {
        try {

            $clearText = $this->kmsClient->decrypt([
                'CiphertextBlob' => base64_decode($ciphertext)
            ]);

            return $clearText->get('Plaintext');

        } catch ( \Exception $e ){
        }

        // else fall back to old style encryption

        $this->getLogger()->warn('RSA decryption still used');

        return $this->bankCipher->decrypt($ciphertext);
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
