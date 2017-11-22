<?php

namespace App\Service;

use Alphagov\Notifications\Client as NotifyClient;
use App\Service\Claim as ClaimService;
use DateTime;
use Doctrine\ORM\EntityManager;
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

    public function notifyAll()
    {
        $start = microtime(true);

        $sql = 'SELECT id FROM claim
                WHERE id IN (SELECT DISTINCT claim_id FROM note WHERE type NOT IN (
                    :noteTypeClaimDuplicateEmailSent,
                    :noteTypeClaimDuplicateTextSent,
                    :noteTypeClaimRejectedEmailSent,
                    :noteTypeClaimRejectedTextSent,
                    :noteTypeClaimAcceptedEmailSent,
                    :noteTypeClaimAcceptedTextSent))
                AND (status IN (:statusDuplicate, :statusRejected) AND finished_datetime < :today)
                OR (status = :acceptedStatus AND payment_id IS NOT NULL)
                ORDER BY finished_datetime ASC;';

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
            $claim = $this->claimService->getClaimEntity($claimId);

            $processedCount++;

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
}