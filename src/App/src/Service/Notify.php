<?php

namespace App\Service;

use Alphagov\Notifications\Client as NotifyClient;
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

        //TODO: Migration script to patch in notifcation note entries

        //Select all claim ids that aren't associated with a notification note type
        //Restrict those to duplicated or rejected claims from yesterday or older
        //Plus any accepted claims with a payment. They will have been added to the SOP1 spreadsheet
        $sql = 'SELECT id, finished_datetime FROM claim
                WHERE id IN (SELECT DISTINCT claim_id FROM note WHERE type NOT IN (
                    :noteTypeClaimDuplicateEmailSent,
                    :noteTypeClaimRejectedEmailSent,
                    :noteTypeClaimAcceptedEmailSent))
                AND ((status IN (:statusDuplicate, :statusRejected) AND finished_datetime < :today) OR (status = :acceptedStatus AND payment_id IS NOT NULL))
                AND json_data->\'contact\'->\'email\' IS NOT NULL UNION
                SELECT id, finished_datetime FROM claim
                WHERE id IN (SELECT DISTINCT claim_id FROM note WHERE type NOT IN (
                    :noteTypeClaimDuplicateTextSent,
                    :noteTypeClaimRejectedTextSent,
                    :noteTypeClaimAcceptedTextSent))
                AND ((status IN (:statusDuplicate, :statusRejected) AND finished_datetime < :today) OR (status = :acceptedStatus AND payment_id IS NOT NULL))
                AND (json_data->\'contact\'->\'phone\' IS NOT NULL AND (json_data->>\'contact\')::json->>\'phone\' LIKE \'07%\')
                ORDER BY finished_datetime';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql,
            [
                'noteTypeClaimDuplicateEmailSent' => NoteModel::TYPE_CLAIM_DUPLICATE_EMAIL_SENT,
                'noteTypeClaimDuplicateTextSent' => NoteModel::TYPE_CLAIM_DUPLICATE_TEXT_SENT,
                'noteTypeClaimRejectedEmailSent' => NoteModel::TYPE_CLAIM_REJECTED_EMAIL_SENT,
                'noteTypeClaimRejectedTextSent' => NoteModel::TYPE_CLAIM_REJECTED_TEXT_SENT,
                'noteTypeClaimAcceptedEmailSent' => NoteModel::TYPE_CLAIM_ACCEPTED_EMAIL_SENT,
                'noteTypeClaimAcceptedTextSent' => NoteModel::TYPE_CLAIM_ACCEPTED_TEXT_SENT,
                'statusDuplicate' => ClaimModel::STATUS_DUPLICATE,
                'statusRejected' => ClaimModel::STATUS_REJECTED,
                'acceptedStatus' => ClaimModel::STATUS_ACCEPTED,
                'today' => (new DateTime('today'))->format('Y-m-d H:i:s')
            ]
        );

        $claimIdsToNotify = $statement->fetchAll();

        $notified = [
            'total' => count($claimIdsToNotify),
            'queryTime' => microtime(true) - $start
        ];

        $this->getLogger()->alert("{$notified['total']} claimants to notify. Query time {$notified['queryTime']}s");

        $processedCount = 0;
        foreach ($claimIdsToNotify as $claimIdToNotify) {
            $claimId = $claimIdToNotify['id'];
            $claim = $this->claimService->get($claimId, $userId);

            $successful = false;
            if ($claim->getStatus() === ClaimModel::STATUS_DUPLICATE) {
                $successful = $this->sendDuplicateNotification($claim);
            } elseif ($claim->getStatus() === ClaimModel::STATUS_REJECTED) {
                $successful = $this->sendRejectionNotification($claim);
            } elseif ($claim->getStatus() === ClaimModel::STATUS_ACCEPTED) {
                $successful = $this->sendAcceptanceNotification($claim);
            }

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

        return $notified;
    }

    private function sendDuplicateNotification(ClaimModel $claim): bool
    {
        return false;
    }

    private function sendRejectionNotification(ClaimModel $claim): bool
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

        switch ($claim->getRejectionReason()) {
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
            case ClaimModel::REJECTION_REASON_OTHER:
            default:
                $sendRejectionMessage = false;
        }

        if ($sendRejectionMessage) {
            $contact = $claim->getApplication()->getContact();
            $contactName = $claim->getApplication()->getApplicant() === 'attorney' ?
                $claim->getApplication()->getAttorney()->getCurrent()->getName()->getFormattedName()
                : $claim->getDonorName();

            if ($contact->hasEmail()) {
                try {
                    $this->notifyClient->sendEmail($contact->getEmail(), '018ab571-a2a5-41e6-a1d4-ae369e2d3cd1', array_merge($emailPersonalisation, [
                        'person-completing' => $contactName,
                        'donor-name' => $claim->getDonorName(),
                        'claim-code' => $claim->getReferenceNumber()
                    ]));

                    $this->getLogger()->info("Successfully sent rejection email for claim {$claim->getReferenceNumber()}");

                    $successful = true;
                } catch (Exception $ex) {
                    $this->getLogger()->warn("Failed to send rejection email for claim {$claim->getReferenceNumber()} due to {$ex->getMessage()}", [$ex]);
                }
            }

            if ($contact->hasPhone() && substr($contact->getPhone(), 0, 2) === '07' && $smsTemplate) {
                try {
                    $this->notifyClient->sendSms($contact->getPhone(), $smsTemplate, [
                        'donor-name' => $claim->getDonorName(),
                        'claim-code' => $claim->getReferenceNumber()
                    ]);

                    $this->getLogger()->info("Successfully sent rejection text for claim {$claim->getReferenceNumber()}");

                    $successful = true;
                } catch (Exception $ex) {
                    $this->getLogger()->warn("Failed to send rejection text for claim {$claim->getReferenceNumber()} due to {$ex->getMessage()}", [$ex]);
                }
            }
        }

        return $successful;
    }

    private function sendAcceptanceNotification(ClaimModel $claim): bool
    {
        $successful = false;

        $contact = $claim->getApplication()->getContact();
        $contactName = $claim->getApplication()->getApplicant() === 'attorney' ?
            $claim->getApplication()->getAttorney()->getCurrent()->getName()->getFormattedName()
            : $claim->getDonorName();

        if ($contact->hasEmail()) {
            try {
                $this->notifyClient->sendEmail($contact->getEmail(), '810b6370-7162-4d9a-859c-34b61f3fecde', [
                    'person-completing' => $contactName,
                    'amount-including-interest' => $claim->getRefundTotalAmountString(),
                    'interest-amount' => $claim->getRefundInterestAmountString(),
                    'donor-name' => $claim->getDonorName(),
                    'claim-code' => $claim->getReferenceNumber()
                ]);

                $this->getLogger()->info("Successfully sent acceptance email for claim {$claim->getReferenceNumber()}");

                $successful = true;
            } catch (Exception $ex) {
                $this->getLogger()->warn("Failed to send acceptance email for claim {$claim->getReferenceNumber()} due to {$ex->getMessage()}", [$ex]);
            }
        }

        if ($contact->hasPhone() && substr($contact->getPhone(), 0, 2) === '07') {
            try {
                $this->notifyClient->sendSms($contact->getPhone(), 'df4ffd99-fcb0-4f77-b001-0c89b666d02f', [
                    'amount-including-interest' => $claim->getRefundTotalAmountString(),
                    'interest-amount' => $claim->getRefundInterestAmountString(),
                    'donor-name' => $claim->getDonorName(),
                    'claim-code' => $claim->getReferenceNumber()
                ]);

                $this->getLogger()->info("Successfully sent acceptance text for claim {$claim->getReferenceNumber()}");

                $successful = true;
            } catch (Exception $ex) {
                $this->getLogger()->warn("Failed to send acceptance text for claim {$claim->getReferenceNumber()} due to {$ex->getMessage()}", [$ex]);
            }
        }

        return $successful;
    }
}