<?php

namespace App\Service;

use Alphagov\Notifications\Client as NotifyClient;
use App\Entity\Cases\Claim as ClaimEntity;
use App\Service\Claim as ClaimService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Note as NoteModel;
use Opg\Refunds\Log\Initializer;

class Notify implements Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;

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

    public function __construct(EntityManager $entityManager, NotifyClient $notifyClient, ClaimService $claimService)
    {
        $this->entityManager = $entityManager;
        $this->notifyClient = $notifyClient;
        $this->claimService = $claimService;
    }

    public function notifyAll(int $userId)
    {
        $start = microtime(true);

        //Select all claim ids that aren't associated with a notification note type
        //Restrict those to duplicated or rejected claims from yesterday or older
        //Plus any accepted claims with a payment. They will have been added to the SOP1 spreadsheet
        $sql = 'SELECT id, finished_datetime FROM claim
                WHERE outcome_email_sent IS NOT TRUE
                AND (json_data->\'contact\'->\'receive-notifications\' IS NULL OR ((json_data->>\'contact\')::json->>\'receive-notifications\')::boolean IS TRUE)
                AND ((status IN (:statusDuplicate, :statusRejected) AND finished_datetime < :today) OR (status = :acceptedStatus AND payment_id IS NOT NULL))
                AND json_data->\'contact\'->\'email\' IS NOT NULL UNION
                SELECT id, finished_datetime FROM claim
                WHERE outcome_text_sent IS NOT TRUE
                AND (json_data->\'contact\'->\'receive-notifications\' IS NULL OR ((json_data->>\'contact\')::json->>\'receive-notifications\')::boolean IS TRUE)
                AND ((status IN (:statusDuplicate, :statusRejected) AND finished_datetime < :today) OR (status = :acceptedStatus AND payment_id IS NOT NULL))
                AND json_data->\'contact\'->\'phone\' IS NOT NULL AND (json_data->>\'contact\')::json->>\'phone\' LIKE \'07%\' UNION
                SELECT id, finished_datetime FROM claim
                WHERE outcome_letter_sent IS NOT TRUE
                AND (json_data->\'contact\'->\'receive-notifications\' IS NULL OR ((json_data->>\'contact\')::json->>\'receive-notifications\')::boolean IS TRUE)
                AND ((status IN (:statusDuplicate, :statusRejected) AND finished_datetime < :today) OR (status = :acceptedStatus AND payment_id IS NOT NULL))
                AND json_data->\'contact\'->\'address\' IS NOT NULL UNION
                SELECT id, finished_datetime FROM claim
                WHERE outcome_phone_called IS NOT TRUE
                AND (json_data->\'contact\'->\'receive-notifications\' IS NULL OR ((json_data->>\'contact\')::json->>\'receive-notifications\')::boolean IS TRUE)
                AND ((status IN (:statusDuplicate, :statusRejected) AND finished_datetime < :today) OR (status = :acceptedStatus AND payment_id IS NOT NULL))
                AND json_data->\'contact\'->\'email\' IS NULL AND json_data->\'contact\'->\'address\' IS NULL
                AND json_data->\'contact\'->\'phone\' IS NOT NULL AND (json_data->>\'contact\')::json->>\'phone\' NOT LIKE \'07%\'
                ORDER BY finished_datetime';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql,
            [
                'statusDuplicate' => ClaimModel::STATUS_DUPLICATE,
                'statusRejected' => ClaimModel::STATUS_REJECTED,
                'acceptedStatus' => ClaimModel::STATUS_ACCEPTED,
                'today' => (new DateTime('today'))->format('Y-m-d H:i:s')
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

            $this->entityManager->flush();

            if ($successful) {
                $processedCount++;
            }

            //Ensure that there are no timeouts by breaking part way through processing. We can then alert the user to try again
            //Ideally there would be a queuing mechanism but for now this will support retries and rudementary batch processing
            $elapsed = microtime(true) - $start;
            if ($elapsed > 10) {
                break;
            }
        }


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
        $contactName = $claimModel->getApplication()->getApplicant() === 'attorney' ?
            $claimModel->getApplication()->getAttorney()->getCurrent()->getName()->getFormattedName()
            : $claimModel->getDonorName();

        if ($claimModel->shouldSendEmail()) {
            try {
                $this->notifyClient->sendEmail($contact->getEmail(), 'a77309f1-2354-4a1b-ab2f-022a79d9f106', [
                    'person-completing' => $contactName,
                    'donor-name' => $claimModel->getDonorName(),
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
                $this->notifyClient->sendSms($contact->getPhone(), 'df57d1b0-c489-4f8f-990c-1bc58f0d44b4', [
                    'donor-name' => $claimModel->getDonorName(),
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
                $smsTemplate = 'f90cdca8-cd8b-4e22-ac66-d328b219f53e';
                break;
            case ClaimModel::REJECTION_REASON_PREVIOUSLY_REFUNDED:
                $emailPersonalisation['poas-already-refunded'] = 'yes';
                $smsTemplate = '5ccfdd66-0040-423a-8426-1458f912d41a';
                break;
            case ClaimModel::REJECTION_REASON_NO_FEES_PAID:
                $emailPersonalisation['no-fees-paid'] = 'yes';
                $smsTemplate = '80b81c91-667e-47d8-bd8e-b87fdfa1b3de';
                break;
            case ClaimModel::REJECTION_REASON_CLAIM_NOT_VERIFIED:
                $emailPersonalisation['details-not-verified'] = 'yes';
                $smsTemplate = '2bb54224-0cab-44b9-9623-fd12f6ee6e77';
                break;
            default:
                $sendRejectionMessage = false;
        }

        if ($sendRejectionMessage) {
            $contact = $claimModel->getApplication()->getContact();
            $contactName = $claimModel->getApplication()->getApplicant() === 'attorney' ?
                $claimModel->getApplication()->getAttorney()->getCurrent()->getName()->getFormattedName()
                : $claimModel->getDonorName();

            if ($claimModel->shouldSendEmail()) {
                try {
                    $this->notifyClient->sendEmail($contact->getEmail(), '018ab571-a2a5-41e6-a1d4-ae369e2d3cd1', array_merge($emailPersonalisation, [
                        'person-completing' => $contactName,
                        'donor-name' => $claimModel->getDonorName(),
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
                        'donor-name' => $claimModel->getDonorName(),
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
        $contactName = $claimModel->getApplication()->getApplicant() === 'attorney' ?
            $claimModel->getApplication()->getAttorney()->getCurrent()->getName()->getFormattedName()
            : $claimModel->getDonorName();

        if ($claimModel->shouldSendEmail()) {
            try {
                $this->notifyClient->sendEmail($contact->getEmail(), '810b6370-7162-4d9a-859c-34b61f3fecde', [
                    'person-completing' => $contactName,
                    'amount-including-interest' => $claimModel->getRefundTotalAmountString(),
                    'interest-amount' => $claimModel->getRefundInterestAmountString(),
                    'donor-name' => $claimModel->getDonorName(),
                    'claim-code' => $claimModel->getReferenceNumber()
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
                $this->notifyClient->sendSms($contact->getPhone(), 'df4ffd99-fcb0-4f77-b001-0c89b666d02f', [
                    'amount-including-interest' => $claimModel->getRefundTotalAmountString(),
                    'interest-amount' => $claimModel->getRefundInterestAmountString(),
                    'donor-name' => $claimModel->getDonorName(),
                    'claim-code' => $claimModel->getReferenceNumber()
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
            } catch (Exception $ex) {
                $this->getLogger()->crit("Failed to send acceptance text for claim {$claimModel->getReferenceNumber()} due to {$ex->getMessage()}", [$ex]);
            }
        }

        return $successful;
    }
}