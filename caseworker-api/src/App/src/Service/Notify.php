<?php

namespace App\Service;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\ApiException as NotifyApiException;
use App\Entity\Cases\Claim as ClaimEntity;
use App\Service\Claim as ClaimService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Opg\Refunds\Caseworker\DataModel\Applications\Application as ApplicationModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Note as NoteModel;
use Opg\Refunds\Log\Initializer;

class Notify implements Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;

    const NOTIFY_TEMPLATE_EMAIL_DUPLICATE_CLAIM                 = 'a77309f1-2354-4a1b-ab2f-022a79d9f106';
    const NOTIFY_TEMPLATE_EMAIL_CLAIM_APPROVED                  = '810b6370-7162-4d9a-859c-34b61f3fecde';
    const NOTIFY_TEMPLATE_EMAIL_CLAIM_APPROVED_CHEQUE           = 'e303f8ef-95b4-4f48-b62c-bccb6a72dcd7';
    const NOTIFY_TEMPLATE_EMAIL_CLAIM_APPROVED_BUILDING_SOCIETY = '4ac0a184-8e7e-4dfa-adf1-35250280238d';
    const NOTIFY_TEMPLATE_EMAIL_REJECTION                       = '018ab571-a2a5-41e6-a1d4-ae369e2d3cd1';

    const NOTIFY_TEMPLATE_SMS_DUPLICATE_CLAIM                   = 'df57d1b0-c489-4f8f-990c-1bc58f0d44b4';
    const NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED                    = 'df4ffd99-fcb0-4f77-b001-0c89b666d02f';
    const NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED_CHEQUE             = '525c6409-dbc3-4b94-bcd0-66e944f93873';
    const NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED_BUILDING_SOCIETY   = 'ec31d721-bbb6-43ad-b06a-1cca9476352d';
    const NOTIFY_TEMPLATE_SMS_REJECTION_NO_ELIGIBLE_POAS_FOUND  = 'f90cdca8-cd8b-4e22-ac66-d328b219f53e';
    const NOTIFY_TEMPLATE_SMS_REJECTION_PREVIOUSLY_REFUNDED     = '5ccfdd66-0040-423a-8426-1458f912d41a';
    const NOTIFY_TEMPLATE_SMS_REJECTION_NO_FEES_PAID            = '80b81c91-667e-47d8-bd8e-b87fdfa1b3de';
    const NOTIFY_TEMPLATE_SMS_REJECTION_CLAIM_NOT_VERIFIED      = '2bb54224-0cab-44b9-9623-fd12f6ee6e77';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var NotifyClient
     */
    private $notifyClient;

    /**
     * @var Claim
     */
    private $claimService;

    /**
     * @var array
     */
    private $maxDonorNameLength = [
        self::NOTIFY_TEMPLATE_SMS_DUPLICATE_CLAIM                  => 126,  //SMS - caseworker - duplicate claim
        self::NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED                   => 175, //SMS - refund approved
        self::NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED_CHEQUE            => 175, //SMS - refund approved - cheque
        self::NOTIFY_TEMPLATE_SMS_REJECTION_NO_ELIGIBLE_POAS_FOUND => 71,  //SMS - rejection - no poas found
        self::NOTIFY_TEMPLATE_SMS_REJECTION_PREVIOUSLY_REFUNDED    => 201, //SMS - rejection - POAs already refunded
        self::NOTIFY_TEMPLATE_SMS_REJECTION_NO_FEES_PAID           => 175, //SMS - rejection - no fees paid
        self::NOTIFY_TEMPLATE_SMS_REJECTION_CLAIM_NOT_VERIFIED     => 124,  //SMS - rejection - details not verified
    ];

    public function __construct(EntityManager $entityManager, NotifyClient $notifyClient, ClaimService $claimService)
    {
        $this->entityManager = $entityManager;
        $this->notifyClient = $notifyClient;
        $this->claimService = $claimService;
    }

    public function notifyAll(int $userId, ?int $timeout = null, ?int $maxNotifications = null)
    {
        $this->getLogger()->info("Notifying claimaints of the outcome of their claim using user id {$userId}, timeout {$timeout}, max number of notifications {$maxNotifications}");

        $start = microtime(true);

        //Select all claim ids that aren't associated with a notification note type
        //Restrict those to duplicated or rejected claims from yesterday or older
        //Plus any accepted claims with a payment. They will have been added to the SOP1 spreadsheet
        $sql = 'SELECT c.id, finished_datetime FROM claim c LEFT OUTER JOIN payment p ON c.id = p.claim_id
                WHERE outcome_email_sent IS NOT TRUE
                AND (json_data->\'contact\'->\'receive-notifications\' IS NULL OR ((json_data->>\'contact\')::json->>\'receive-notifications\')::boolean IS TRUE)
                AND ((status IN (:statusDuplicate, :statusRejected) AND finished_datetime < :today) OR (status = :acceptedStatus AND p.id IS NOT NULL))
                AND json_data->\'contact\'->\'email\' IS NOT NULL UNION
                SELECT c.id, finished_datetime FROM claim c LEFT OUTER JOIN payment p ON c.id = p.claim_id
                WHERE outcome_text_sent IS NOT TRUE
                AND (json_data->\'contact\'->\'receive-notifications\' IS NULL OR ((json_data->>\'contact\')::json->>\'receive-notifications\')::boolean IS TRUE)
                AND ((status IN (:statusDuplicate, :statusRejected) AND finished_datetime < :today) OR (status = :acceptedStatus AND p.id IS NOT NULL))
                AND json_data->\'contact\'->\'phone\' IS NOT NULL AND regexp_replace((json_data->>\'contact\')::json->>\'phone\', \'^[+]?[0]*44\', \'0\') SIMILAR TO \'07[1-9]%\' UNION
                SELECT c.id, finished_datetime FROM claim c LEFT OUTER JOIN payment p ON c.id = p.claim_id
                WHERE outcome_letter_sent IS NOT TRUE
                AND (json_data->\'contact\'->\'receive-notifications\' IS NULL OR ((json_data->>\'contact\')::json->>\'receive-notifications\')::boolean IS TRUE)
                AND ((status IN (:statusDuplicate, :statusRejected) AND finished_datetime < :today) OR (status = :acceptedStatus AND p.id IS NOT NULL))
                AND json_data->\'contact\'->\'email\' IS NULL AND json_data->\'contact\'->\'phone\' IS NULL
                AND json_data->\'contact\'->\'address\' IS NOT NULL UNION
                SELECT c.id, finished_datetime FROM claim c LEFT OUTER JOIN payment p ON c.id = p.claim_id
                WHERE outcome_phone_called IS NOT TRUE
                AND (json_data->\'contact\'->\'receive-notifications\' IS NULL OR ((json_data->>\'contact\')::json->>\'receive-notifications\')::boolean IS TRUE)
                AND ((status IN (:statusDuplicate, :statusRejected) AND finished_datetime < :today) OR (status = :acceptedStatus AND p.id IS NOT NULL))
                AND json_data->\'contact\'->\'email\' IS NULL AND json_data->\'contact\'->\'address\' IS NULL
                AND json_data->\'contact\'->\'phone\' IS NOT NULL AND regexp_replace((json_data->>\'contact\')::json->>\'phone\', \'^[+]?[0]*44\', \'0\') NOT SIMILAR TO \'07[1-9]%\'
                ORDER BY finished_datetime';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql,
            [
                'statusDuplicate' => ClaimModel::STATUS_DUPLICATE,
                'statusRejected' => ClaimModel::STATUS_REJECTED,
                'acceptedStatus' => ClaimModel::STATUS_ACCEPTED,
                'today' => (new DateTime('today'))->format('Y-m-d')
            ]
        );

        $claimIdsToNotify = $statement->fetchAll();

        $notified = [
            'total' => count($claimIdsToNotify),
            'queryTime' => round(microtime(true) - $start, 4)
        ];

        $letters = [];
        $phoneCalls = [];

        $this->getLogger()->info("{$notified['total']} claimants to notify. Query time {$notified['queryTime']}s");

        $startNotify = microtime(true);

        $processedCount = 0;
        foreach ($claimIdsToNotify as $claimIdToNotify) {
            $claimId = $claimIdToNotify['id'];
            $claimModel = $this->claimService->get($claimId, $userId);
            $claimEntity = $this->claimService->getClaimEntity($claimId);

            $successful = false;

            if ($claimModel->shouldSendLetter()) {
                //Address only. Manual letter required
                $letters[$claimModel->getId()] = [
                    'claimCode' => $claimModel->getReferenceNumber(),
                    'donorName' => $claimModel->getDonorName(),
                    'outcome' => $claimModel->getStatusText()
                ];
            } elseif ($claimModel->shouldPhone()) {
                //Land line phone number only. Phone call required
                $phoneCalls[$claimModel->getId()] = [
                    'claimCode' => $claimModel->getReferenceNumber(),
                    'donorName' => $claimModel->getDonorName(),
                    'outcome' => $claimModel->getStatusText()
                ];
            } elseif ($claimModel->getStatus() === ClaimModel::STATUS_DUPLICATE) {
                $successful = $this->sendDuplicateNotification($claimModel, $claimEntity, $userId);
            } elseif ($claimModel->getStatus() === ClaimModel::STATUS_REJECTED) {
                $successful = $this->sendRejectionNotification($claimModel, $claimEntity, $userId);
            } elseif ($claimModel->getStatus() === ClaimModel::STATUS_ACCEPTED) {
                $successful = $this->sendAcceptanceNotification($claimModel, $claimEntity, $userId);
            }

            if ($successful) {
                $processedCount++;
            }

            //Ensure that there are no timeouts by breaking part way through processing. We can then alert the user to try again
            //Ideally there would be a queuing mechanism but for now this will support retries and rudementary batch processing
            $elapsed = microtime(true) - $start;
            if ($timeout !== null && $elapsed > $timeout) {
                break;
            }

            if ($maxNotifications !== null && $processedCount >= $maxNotifications) {
                break;
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $notified['processed'] = $processedCount;
        $notified['notifyTime'] = round(microtime(true) - $startNotify, 4);

        //Only send back letter and phone call details if there are no more emails or texts to send
        //so that sending the letters and making the calls are the last tasks to complete
        if ($notified['total'] - $notified['processed'] - count($letters) - count($phoneCalls) === 0) {
            $notified['letters'] = $letters;
            $notified['phoneCalls'] = $phoneCalls;
        } else {
            $notified['letters'] = [];
            $notified['phoneCalls'] = [];
        }

        return $notified;
    }

    private function sendDuplicateNotification(ClaimModel $claimModel, ClaimEntity $claimEntity, int $userId): bool
    {
        $successful = false;

        $contact = $claimModel->getApplication()->getContact();
        $contactName = $this->getContactName($claimModel);
        $donorDob = date('j F Y', $claimModel->getApplication()->getDonor()->getCurrent()->getDob()->getTimestamp());

        if ($claimModel->shouldSendEmail()) {
            try {
                $templateId = self::NOTIFY_TEMPLATE_EMAIL_DUPLICATE_CLAIM;
                $this->notifyClient->sendEmail($contact->getEmail(), $templateId, [
                    'person-completing' => $contactName,
                    'donor-name' => $this->getDonorNameForTemplate($templateId, $claimModel->getDonorName()),
                    'donor-dob' => $donorDob,
                    'claim-code' => $claimModel->getReferenceNumber()
                ]);

                $this->getLogger()->info("Successfully sent duplicate claim email for claim {$claimModel->getReferenceNumber()}");

                $this->claimService->addNote(
                    $claimModel->getId(),
                    $userId,
                    NoteModel::TYPE_CLAIM_DUPLICATE_EMAIL_SENT,
                    'Successfully sent duplicate claim email to ' . $contact->getEmail()
                );

                $claimEntity->setOutcomeEmailSent(true);

                $successful = true;
            } catch (Exception $ex) {
                $this->getLogger()->crit("Failed to send duplicate claim email for claim {$claimModel->getReferenceNumber()} due to {$ex->getMessage()}", [$ex]);
            }
        }

        if ($claimModel->shouldSendText()) {
            try {
                $templateId = self::NOTIFY_TEMPLATE_SMS_DUPLICATE_CLAIM;
                $this->notifyClient->sendSms($contact->getPhone(), $templateId, [
                    'donor-name' => $this->getDonorNameForTemplate($templateId, $claimModel->getDonorName()),
                    'donor-dob' => $donorDob,
                    'claim-code' => $claimModel->getReferenceNumber()
                ]);

                $this->getLogger()->info("Successfully sent duplicate claim text for claim {$claimModel->getReferenceNumber()}");

                $this->claimService->addNote(
                    $claimModel->getId(),
                    $userId,
                    NoteModel::TYPE_CLAIM_DUPLICATE_TEXT_SENT,
                    'Successfully sent duplicate claim text to ' . $contact->getPhone()
                );

                $claimEntity->setOutcomeTextSent(true);

                $successful = true;
            } catch (NotifyApiException $ex) {
                if (strpos($ex->getMessage(), 'phone_number') !== false) {
                    $this->getLogger()->warn("Phone number {$contact->getPhone()} for claim {$claimModel->getReferenceNumber()} failed validation so duplicate text cannot be sent. Marking claim as text sent to prevent retry. {$ex->getMessage()}", [$ex]);
                    $claimEntity->setOutcomeTextSent(true);

                    $successful = true;
                } else {
                    $this->getLogger()->err("Failed to send duplicate text for claim {$claimModel->getReferenceNumber()} due to {$ex->getMessage()}. Please check if this is a recoverable error", [$ex]);
                }
            } catch (Exception $ex) {
                $this->getLogger()->crit("Failed to send duplicate claim text for claim {$claimModel->getReferenceNumber()} due to {$ex->getMessage()}", [$ex]);
            }
        }

        return $successful;
    }

    private function sendRejectionNotification(ClaimModel $claimModel, ClaimEntity $claimEntity, int $userId): bool
    {
        $successful = false;

        $sendRejectionMessage = true;
        $smsTemplate = false;

        $emailPersonalisation = [
            'no-poas-found'         => 'no',
            'no-fees-paid'          => 'no',
            'poas-already-refunded' => 'no',
            'details-not-verified'  => 'no',
        ];

        switch ($claimModel->getRejectionReason()) {
            case ClaimModel::REJECTION_REASON_NO_ELIGIBLE_POAS_FOUND:
                $emailPersonalisation['no-poas-found'] = 'yes';
                $smsTemplate = self::NOTIFY_TEMPLATE_SMS_REJECTION_NO_ELIGIBLE_POAS_FOUND;
                break;
            case ClaimModel::REJECTION_REASON_PREVIOUSLY_REFUNDED:
                $emailPersonalisation['poas-already-refunded'] = 'yes';
                $smsTemplate = self::NOTIFY_TEMPLATE_SMS_REJECTION_PREVIOUSLY_REFUNDED;
                break;
            case ClaimModel::REJECTION_REASON_NO_FEES_PAID:
                $emailPersonalisation['no-fees-paid'] = 'yes';
                $smsTemplate = self::NOTIFY_TEMPLATE_SMS_REJECTION_NO_FEES_PAID;
                break;
            case ClaimModel::REJECTION_REASON_CLAIM_NOT_VERIFIED:
                $emailPersonalisation['details-not-verified'] = 'yes';
                $smsTemplate = self::NOTIFY_TEMPLATE_SMS_REJECTION_CLAIM_NOT_VERIFIED;
                break;
            default:
                $sendRejectionMessage = false;
        }

        if ($sendRejectionMessage) {
            $contact = $claimModel->getApplication()->getContact();
            $contactName = $this->getContactName($claimModel);
            $donorDob = date('j F Y', $claimModel->getApplication()->getDonor()->getCurrent()->getDob()->getTimestamp());

            if ($claimModel->shouldSendEmail()) {
                try {
                    $templateId = self::NOTIFY_TEMPLATE_EMAIL_REJECTION;
                    $this->notifyClient->sendEmail($contact->getEmail(), $templateId, array_merge($emailPersonalisation, [
                        'person-completing' => $contactName,
                        'donor-name' => $this->getDonorNameForTemplate($templateId, $claimModel->getDonorName()),
                        'donor-dob' => $donorDob,
                        'claim-code' => $claimModel->getReferenceNumber()
                    ]));

                    $this->getLogger()->info("Successfully sent rejection email for claim {$claimModel->getReferenceNumber()}");

                    $this->claimService->addNote(
                        $claimModel->getId(),
                        $userId,
                        NoteModel::TYPE_CLAIM_REJECTED_EMAIL_SENT,
                        'Successfully sent rejection email to ' . $contact->getEmail()
                    );

                    $claimEntity->setOutcomeEmailSent(true);

                    $successful = true;
                } catch (Exception $ex) {
                    $this->getLogger()->crit("Failed to send rejection email for claim {$claimModel->getReferenceNumber()} due to {$ex->getMessage()}", [$ex]);
                }
            }

            if ($claimModel->shouldSendText() && $smsTemplate) {
                try {
                    $this->notifyClient->sendSms($contact->getPhone(), $smsTemplate, [
                        'donor-name' => $this->getDonorNameForTemplate($smsTemplate, $claimModel->getDonorName()),
                        'donor-dob' => $donorDob,
                        'claim-code' => $claimModel->getReferenceNumber()
                    ]);

                    $this->getLogger()->info("Successfully sent rejection text for claim {$claimModel->getReferenceNumber()}");

                    $this->claimService->addNote(
                        $claimModel->getId(),
                        $userId,
                        NoteModel::TYPE_CLAIM_REJECTED_TEXT_SENT,
                        'Successfully sent rejection text to ' . $contact->getPhone()
                    );

                    $claimEntity->setOutcomeTextSent(true);

                    $successful = true;
                } catch (NotifyApiException $ex) {
                    if (strpos($ex->getMessage(), 'phone_number') !== false) {
                        $this->getLogger()->warn("Phone number {$contact->getPhone()} for claim {$claimModel->getReferenceNumber()} failed validation so rejection text cannot be sent. Marking claim as text sent to prevent retry. {$ex->getMessage()}", [$ex]);
                        $claimEntity->setOutcomeTextSent(true);

                        $successful = true;
                    } else {
                        $this->getLogger()->err("Failed to send rejection text for claim {$claimModel->getReferenceNumber()} due to {$ex->getMessage()}. Please check if this is a recoverable error", [$ex]);
                    }
                } catch (Exception $ex) {
                    $this->getLogger()->crit("Failed to send rejection text for claim {$claimModel->getReferenceNumber()} due to {$ex->getMessage()}", [$ex]);
                }
            }
        }

        return $successful;
    }

    private function sendAcceptanceNotification(ClaimModel $claimModel, ClaimEntity $claimEntity, int $userId): bool
    {
        $successful = false;

        $contact = $claimModel->getApplication()->getContact();
        $contactName = $this->getContactName($claimModel);
        $donorDob = date('j F Y', $claimModel->getApplication()->getDonor()->getCurrent()->getDob()->getTimestamp());

        $isRefundByCheque = $claimModel->getApplication()->isRefundByCheque()
            || !array_key_exists('details', $claimEntity->getJsonData()['account']);
        $isBuildingSociety = $claimModel->getApplication()->getAccount() !== null
            && $claimModel->getApplication()->getAccount()->isBuildingSociety();

        if ($claimModel->shouldSendEmail()) {
            try {
                $templateId = $isRefundByCheque ?
                    ($isBuildingSociety ? self::NOTIFY_TEMPLATE_EMAIL_CLAIM_APPROVED_BUILDING_SOCIETY
                        : self::NOTIFY_TEMPLATE_EMAIL_CLAIM_APPROVED_CHEQUE)
                    : self::NOTIFY_TEMPLATE_EMAIL_CLAIM_APPROVED;

                if ($claimModel->getApplication()->getAccount() !== null && $claimModel->getApplication()->getAccount()->isBuildingSociety()) {
                    $templateId = self::NOTIFY_TEMPLATE_EMAIL_CLAIM_APPROVED_BUILDING_SOCIETY;
                }

                $this->notifyClient->sendEmail($contact->getEmail(), $templateId, [
                    'person-completing' => $contactName,
                    'amount-including-interest' => $claimModel->getRefundTotalAmountString(),
                    'interest-amount' => $claimModel->getRefundInterestAmountString(),
                    'donor-name' => $this->getDonorNameForTemplate($templateId, $claimModel->getDonorName()),
                    'donor-dob' => $donorDob,
                    'claim-code' => $claimModel->getReferenceNumber(),
                    'number-found' => count($claimModel->getPoas())
                ]);

                $this->getLogger()->info("Successfully sent acceptance email for claim {$claimModel->getReferenceNumber()}");

                $this->claimService->addNote(
                    $claimModel->getId(),
                    $userId,
                    NoteModel::TYPE_CLAIM_ACCEPTED_EMAIL_SENT,
                    'Successfully sent acceptance email to ' . $contact->getEmail()
                );

                $claimEntity->setOutcomeEmailSent(true);

                $successful = true;
            } catch (Exception $ex) {
                $this->getLogger()->crit("Failed to send acceptance email for claim {$claimModel->getReferenceNumber()} due to {$ex->getMessage()}", [$ex]);
            }
        }

        if ($claimModel->shouldSendText()) {
            try {
                $templateId = $isRefundByCheque ?
                    ($isBuildingSociety ? self::NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED_BUILDING_SOCIETY
                        : self::NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED_CHEQUE)
                    : self::NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED;

                $this->notifyClient->sendSms($contact->getPhone(), $templateId, [
                    'amount-including-interest' => $claimModel->getRefundTotalAmountString(),
                    'interest-amount' => $claimModel->getRefundInterestAmountString(),
                    'donor-name' => $this->getDonorNameForTemplate($templateId, $claimModel->getDonorName()),
                    'donor-dob' => $donorDob,
                    'claim-code' => $claimModel->getReferenceNumber(),
                    'number-found' => count($claimModel->getPoas())
                ]);

                $this->getLogger()->info("Successfully sent acceptance text for claim {$claimModel->getReferenceNumber()}");

                $this->claimService->addNote(
                    $claimModel->getId(),
                    $userId,
                    NoteModel::TYPE_CLAIM_ACCEPTED_TEXT_SENT,
                    'Successfully sent acceptance text to ' . $contact->getPhone()
                );

                $claimEntity->setOutcomeTextSent(true);

                $successful = true;
            } catch (NotifyApiException $ex) {
                if (strpos($ex->getMessage(), 'phone_number') !== false) {
                    $this->getLogger()->warn("Phone number {$contact->getPhone()} for claim {$claimModel->getReferenceNumber()} failed validation so acceptance text cannot be sent. Marking claim as text sent to prevent retry. {$ex->getMessage()}", [$ex]);
                    $claimEntity->setOutcomeTextSent(true);

                    $successful = true;
                } else {
                    $this->getLogger()->err("Failed to send acceptance text for claim {$claimModel->getReferenceNumber()} due to {$ex->getMessage()}. Please check if this is a recoverable error", [$ex]);
                }
            } catch (Exception $ex) {
                $this->getLogger()->crit("Failed to send acceptance text for claim {$claimModel->getReferenceNumber()} due to {$ex->getMessage()}", [$ex]);
            }
        }

        return $successful;
    }

    public function getDonorNameForTemplate(string $templateId, string $donorName)
    {
        if (array_key_exists($templateId, $this->maxDonorNameLength)) {
            return substr($donorName, 0, $this->maxDonorNameLength[$templateId] - 1);
        }

        return $donorName;
    }

    /**
     * @param ClaimModel $claimModel
     * @return string
     */
    private function getContactName(ClaimModel $claimModel): string
    {
        $contactName = '';

        switch ($claimModel->getApplication()->getApplicant()) {
            case ApplicationModel::APPLICANT_DONOR:
                $contactName = $claimModel->getDonorName();
                break;
            case ApplicationModel::APPLICANT_ATTORNEY:
                $contactName = $claimModel->getApplication()->getAttorney()->getCurrent()->getName()
                    ->getFormattedName();
                break;
            case ApplicationModel::APPLICANT_EXECUTOR:
                $contactName = $claimModel->getApplication()->getExecutor()->getName()->getFormattedName();
                break;
        }

        return $contactName;
    }
}