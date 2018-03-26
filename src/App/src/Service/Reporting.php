<?php

namespace App\Service;

use App\Entity\Cases\Report as ReportEntity;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Caseworker\DataModel\Applications\AssistedDigital as AssistedDigitalModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;

/**
 * Class Reporting
 * @package App\Service
 */
class Reporting
{
    const GENERATED_DATE_FORMAT = 'd/m/Y H:i:s';
    const SQL_DATE_FORMAT = 'Y-m-d H:i:s';
    const SHORT_CACHE_MODIFIER = '+5 seconds';
    const MEDIUM_CACHE_MODIFIER = '+5 minutes';
    const LONG_CACHE_MODIFIER = '+1 hour';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $reportRepository;

    public function __construct(EntityManager $claimsEntityManager)
    {
        $this->entityManager = $claimsEntityManager;
        $this->reportRepository = $this->entityManager->getRepository(ReportEntity::class);
    }

    public function getAllReports()
    {
        $start = microtime(true);

        $dateOfFirstClaim = new DateTime($this->entityManager->getConnection()->executeQuery(
            'SELECT received_datetime FROM claim ORDER BY received_datetime LIMIT 1'
        )->fetch()['received_datetime']);

        $reports = [
            'claim' => $this->getClaimReport($dateOfFirstClaim),
            'claimSource' => $this->getClaimSourceReport($dateOfFirstClaim),
            'phoneClaimType' => $this->getPhoneClaimTypeReport($dateOfFirstClaim),
            'rejectionReason' => $this->getRejectionReasonReport($dateOfFirstClaim),
            'duplicateBankDetail' => $this->getDuplicateBankDetailReport($dateOfFirstClaim),
            'refund' => $this->getRefundReport($dateOfFirstClaim),
            'processingTime' => $this->getProcessingTime($dateOfFirstClaim),
            'completionTime' => $this->getCompletionTime($dateOfFirstClaim),
            'notifications' => $this->getNotifications($dateOfFirstClaim),
            'poasPerClaim' => $this->getPoasPerClaim($dateOfFirstClaim),
        ];

        $this->entityManager->flush();
        $this->entityManager->clear();

        $end = microtime(true);

        $reports['generated'] = date('d/m/Y H:i:s', (new DateTime())->getTimestamp());
        $reports['generationTimeInMs'] = round(($end - $start) * 1000);

        return $reports;
    }

    public function getClaimReport(DateTime $dateOfFirstClaim)
    {
        /** @var ReportEntity $claimAllTime */
        $claimAllTime = $this->reportRepository->findOneBy(['type' => 'claim', 'startDateTime' => $dateOfFirstClaim]);

        if ($claimAllTime === null || $claimAllTime->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime()) {
            //Generate stat
            $startMicroTime = microtime(true);

            $sql = 'SELECT status, count(*) FROM claim GROUP BY status UNION ALL
                    SELECT \'total\', count(*) FROM claim UNION ALL
                    SELECT \'outcome_changed\', COUNT(DISTINCT(claim_id)) FROM note WHERE type = \'claim_outcome_changed\'';

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql
            );

            $data = $this->addStatusColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));
            $endDateTime = new DateTime();

            $claimAllTime = $this->upsertReport(
                $claimAllTime,
                'claim',
                'All time',
                $dateOfFirstClaim,
                $endDateTime,
                $data,
                $startMicroTime
            );
        }

        $allTime = $claimAllTime->getData();

        $sql = 'SELECT status, count(*) FROM claim WHERE status = :statusPending AND received_datetime >= :startOfDay AND received_datetime <= :endOfDay GROUP BY status UNION ALL
                SELECT status, count(*) FROM claim WHERE status = :statusInProgress AND updated_datetime >= :startOfDay AND updated_datetime <= :endOfDay GROUP BY status UNION ALL
                SELECT status, count(*) FROM claim WHERE status IN (:statusDuplicate, :statusRejected, :statusAccepted, :statusWithdrawn) AND finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay GROUP BY status UNION ALL
                SELECT \'total\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay UNION ALL
                SELECT \'outcome_changed\', COUNT(DISTINCT(c.id)) FROM claim c LEFT JOIN note n ON c.id = n.claim_id WHERE n.created_datetime >= :startOfDay AND n.created_datetime <= :endOfDay AND n.type = \'claim_outcome_changed\'';

        $parameters = [
            'statusPending' => ClaimModel::STATUS_PENDING,
            'statusInProgress' => ClaimModel::STATUS_IN_PROGRESS,
            'statusDuplicate' => ClaimModel::STATUS_DUPLICATE,
            'statusRejected' => ClaimModel::STATUS_REJECTED,
            'statusAccepted' => ClaimModel::STATUS_ACCEPTED,
            'statusWithdrawn' => ClaimModel::STATUS_WITHDRAWN
        ];

        $byDay = [];
        $startOfDay = new DateTime('today');
        $endOfDay = (clone $startOfDay)->add(new DateInterval('P1D'));
        for ($i = 0; $i < 45; $i++) {
            if ($endOfDay < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $claimByDay */
            $claimByDay = $this->reportRepository->findOneBy(['type' => 'claim', 'startDateTime' => $startOfDay, 'endDateTime' => $endOfDay]);
            if ($claimByDay === null || ($i < 7 && $claimByDay->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $parameters['startOfDay'] = $startOfDay->format(self::SQL_DATE_FORMAT);
                $parameters['endOfDay'] = $endOfDay->format(self::SQL_DATE_FORMAT);

                $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

                $day = $this->addStatusColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

                $claimByDay = $this->upsertReport(
                    $claimByDay,
                    'claim',
                    date('D d/m/Y', $startOfDay->getTimestamp()),
                    $startOfDay,
                    $endOfDay,
                    $day,
                    $startMicroTime
                );
            }

            $byDay[$claimByDay->getTitle()] = $claimByDay->getData();

            $startOfDay = $startOfDay->sub(new DateInterval('P1D'));
            $endOfDay = $endOfDay->sub(new DateInterval('P1D'));
        }

        $byWeek = [];
        $startOfWeek = new DateTime('last monday');
        $endOfWeek = (clone $startOfWeek)->add(new DateInterval('P1W'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfWeek < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $claimByWeek */
            $claimByWeek = $this->reportRepository->findOneBy(['type' => 'claim', 'startDateTime' => $startOfWeek, 'endDateTime' => $endOfWeek]);
            if ($claimByWeek === null || ($i < 2 && $claimByWeek->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $parameters['startOfDay'] = $startOfWeek->format(self::SQL_DATE_FORMAT);
                $parameters['endOfDay'] = $endOfWeek->format(self::SQL_DATE_FORMAT);

                $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

                $week = $this->addStatusColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

                $claimByWeek = $this->upsertReport(
                    $claimByWeek,
                    'claim',
                    date('D d/m/Y', $startOfWeek->getTimestamp()) . ' - ' . date('D d/m/Y', $endOfWeek->getTimestamp() - 1),
                    $startOfWeek,
                    $endOfWeek,
                    $week,
                    $startMicroTime
                );
            }

            $byWeek[$claimByWeek->getTitle()] = $claimByWeek->getData();

            $startOfWeek = $startOfWeek->sub(new DateInterval('P1W'));
            $endOfWeek = $endOfWeek->sub(new DateInterval('P1W'));
        }

        $byMonth = [];
        $startOfMonth = new DateTime('midnight first day of this month');
        $endOfMonth = (clone $startOfMonth)->add(new DateInterval('P1M'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfMonth < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $claimByMonth */
            $claimByMonth = $this->reportRepository->findOneBy(['type' => 'claim', 'startDateTime' => $startOfMonth, 'endDateTime' => $endOfMonth]);
            if ($claimByMonth === null || ($i < 2 && $claimByMonth->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $parameters['startOfDay'] = $startOfMonth->format(self::SQL_DATE_FORMAT);
                $parameters['endOfDay'] = $endOfMonth->format(self::SQL_DATE_FORMAT);

                $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

                $month = $this->addStatusColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

                $claimByMonth = $this->upsertReport(
                    $claimByMonth,
                    'claim',
                    date('F Y', $startOfMonth->getTimestamp()),
                    $startOfMonth,
                    $endOfMonth,
                    $month,
                    $startMicroTime
                );
            }

            $byMonth[$claimByMonth->getTitle()] = $claimByMonth->getData();

            $startOfMonth = $startOfMonth->sub(new DateInterval('P1M'));
            $endOfMonth = $endOfMonth->sub(new DateInterval('P1M'));
        }

        return [
            'allTime' => $allTime,
            'byDay'   => $byDay,
            'byWeek'  => $byWeek,
            'byMonth' => $byMonth
        ];
    }

    private function addStatusColumns(array $counts)
    {
        if (empty($counts[ClaimModel::STATUS_PENDING])) {
            $counts[ClaimModel::STATUS_PENDING] = 0;
        }
        if (empty($counts[ClaimModel::STATUS_IN_PROGRESS])) {
            $counts[ClaimModel::STATUS_IN_PROGRESS] = 0;
        }
        if (empty($counts[ClaimModel::STATUS_DUPLICATE])) {
            $counts[ClaimModel::STATUS_DUPLICATE] = 0;
        }
        if (empty($counts[ClaimModel::STATUS_REJECTED])) {
            $counts[ClaimModel::STATUS_REJECTED] = 0;
        }
        if (empty($counts[ClaimModel::STATUS_ACCEPTED])) {
            $counts[ClaimModel::STATUS_ACCEPTED] = 0;
        }
        if (empty($counts[ClaimModel::STATUS_WITHDRAWN])) {
            $counts[ClaimModel::STATUS_WITHDRAWN] = 0;
        }
        if (empty($counts['total'])) {
            $counts['total'] = 0;
        }
        if (empty($counts['outcome_changed'])) {
            $counts['outcome_changed'] = 0;
        }

        return $counts;
    }

    public function getClaimSourceReport(DateTime $dateOfFirstClaim)
    {
        /** @var ReportEntity $claimAllTime */
        $claimSourceAllTime = $this->reportRepository->findOneBy(['type' => 'claimSource', 'startDateTime' => $dateOfFirstClaim]);

        if ($claimSourceAllTime === null || $claimSourceAllTime->getGeneratedDateTime()->modify(self::LONG_CACHE_MODIFIER) < new DateTime()) {
            //Generate stat
            $startMicroTime = microtime(true);

            $sql = 'SELECT \'donor\', count(*) FROM claim WHERE json_data->>\'applicant\' = \'donor\' UNION ALL
                    SELECT \'attorney\', count(*) FROM claim WHERE json_data->>\'applicant\' = \'attorney\' UNION ALL
                    SELECT \'assisted_digital\', count(*) FROM claim WHERE json_data->\'ad\' IS NOT NULL UNION ALL
                    SELECT \'donor_deceased\', count(*) FROM claim WHERE json_data->>\'deceased\' = \'true\' UNION ALL
                    SELECT \'total\', count(*) FROM claim';

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql
            );

            $data = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
            $endDateTime = new DateTime();

            $claimSourceAllTime = $this->upsertReport(
                $claimSourceAllTime,
                'claimSource',
                'All time',
                $dateOfFirstClaim,
                $endDateTime,
                $data,
                $startMicroTime
            );
        }

        $allTime = $claimSourceAllTime->getData();

        $sql = 'SELECT \'donor\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay AND json_data->>\'applicant\' = \'donor\' UNION ALL
                SELECT \'attorney\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay AND json_data->>\'applicant\' = \'attorney\' UNION ALL
                SELECT \'assisted_digital\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay AND json_data->\'ad\' IS NOT NULL UNION ALL
                SELECT \'donor_deceased\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay AND json_data->>\'deceased\' = \'true\' UNION ALL
                SELECT \'total\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay';

        $byDay = [];
        $startOfDay = new DateTime('today');
        $endOfDay = (clone $startOfDay)->add(new DateInterval('P1D'));
        for ($i = 0; $i < 45; $i++) {
            if ($endOfDay < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $claimSourceByDay */
            $claimSourceByDay = $this->reportRepository->findOneBy(['type' => 'claimSource', 'startDateTime' => $startOfDay, 'endDateTime' => $endOfDay]);
            if ($claimSourceByDay === null || ($i < 7 && $claimSourceByDay->getGeneratedDateTime()->modify(self::LONG_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $statement = $this->entityManager->getConnection()->executeQuery(
                    $sql,
                    [
                        'startOfDay' => $startOfDay->format(self::SQL_DATE_FORMAT),
                        'endOfDay' => $endOfDay->format(self::SQL_DATE_FORMAT)
                    ]
                );

                $day = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

                $claimSourceByDay = $this->upsertReport(
                    $claimSourceByDay,
                    'claimSource',
                    date('D d/m/Y', $startOfDay->getTimestamp()),
                    $startOfDay,
                    $endOfDay,
                    $day,
                    $startMicroTime
                );
            }

            $byDay[$claimSourceByDay->getTitle()] = $claimSourceByDay->getData();

            $startOfDay = $startOfDay->sub(new DateInterval('P1D'));
            $endOfDay = $endOfDay->sub(new DateInterval('P1D'));
        }

        $byMonth = [];
        $startOfMonth = new DateTime('midnight first day of this month');
        $endOfMonth = (clone $startOfMonth)->add(new DateInterval('P1M'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfMonth < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $claimSourceByMonth */
            $claimSourceByMonth = $this->reportRepository->findOneBy(['type' => 'claimSource', 'startDateTime' => $startOfMonth, 'endDateTime' => $endOfMonth]);
            if ($claimSourceByMonth === null || ($i < 2 && $claimSourceByMonth->getGeneratedDateTime()->modify(self::LONG_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $statement = $this->entityManager->getConnection()->executeQuery(
                    $sql,
                    [
                        'startOfDay' => $startOfMonth->format(self::SQL_DATE_FORMAT),
                        'endOfDay' => $endOfMonth->format(self::SQL_DATE_FORMAT)
                    ]
                );

                $month = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

                $claimSourceByMonth = $this->upsertReport(
                    $claimSourceByMonth,
                    'claimSource',
                    date('F Y', $startOfMonth->getTimestamp()),
                    $startOfMonth,
                    $endOfMonth,
                    $month,
                    $startMicroTime
                );
            }

            $byMonth[$claimSourceByMonth->getTitle()] = $claimSourceByMonth->getData();

            $startOfMonth = $startOfMonth->sub(new DateInterval('P1M'));
            $endOfMonth = $endOfMonth->sub(new DateInterval('P1M'));
        }

        return [
            'allTime' => $allTime,
            'byDay'   => $byDay,
            'byMonth' => $byMonth
        ];
    }

    public function getPhoneClaimTypeReport(DateTime $dateOfFirstClaim)
    {
        /** @var ReportEntity $phoneClaimTypeAllTime */
        $phoneClaimTypeAllTime = $this->reportRepository->findOneBy(['type' => 'phoneClaimType', 'startDateTime' => $dateOfFirstClaim]);

        if ($phoneClaimTypeAllTime === null || $phoneClaimTypeAllTime->getGeneratedDateTime()->modify(self::LONG_CACHE_MODIFIER) < new DateTime()) {
            //Generate stat
            $startMicroTime = microtime(true);

            $sql = 'SELECT json_data->\'ad\'->\'meta\'->>\'type\' AS type, count(*) FROM claim WHERE json_data->\'ad\'->\'meta\'->\'type\' IS NOT NULL GROUP BY type
                    UNION ALL SELECT \'total\', count(*) FROM claim WHERE json_data->\'ad\'->\'meta\'->\'type\' IS NOT NULL';

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql
            );

            $data = $this->addPhoneClaimTypeColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));
            $endDateTime = new DateTime();

            $phoneClaimTypeAllTime = $this->upsertReport(
                $phoneClaimTypeAllTime,
                'phoneClaimType',
                'All time',
                $dateOfFirstClaim,
                $endDateTime,
                $data,
                $startMicroTime
            );
        }

        $allTime = $phoneClaimTypeAllTime->getData();

        $sql = 'SELECT json_data->\'ad\'->\'meta\'->>\'type\' AS type, count(*) FROM claim WHERE json_data->\'ad\'->\'meta\'->\'type\' IS NOT NULL AND received_datetime >= :startOfDay AND received_datetime <= :endOfDay GROUP BY type
                UNION ALL SELECT \'total\', count(*) FROM claim WHERE json_data->\'ad\'->\'meta\'->\'type\' IS NOT NULL AND received_datetime >= :startOfDay AND received_datetime <= :endOfDay';

        $byDay = [];
        $startOfDay = new DateTime('today');
        $endOfDay = (clone $startOfDay)->add(new DateInterval('P1D'));
        for ($i = 0; $i < 45; $i++) {
            if ($endOfDay < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $phoneClaimTypeByDay */
            $phoneClaimTypeByDay = $this->reportRepository->findOneBy(['type' => 'phoneClaimType', 'startDateTime' => $startOfDay, 'endDateTime' => $endOfDay]);
            if ($phoneClaimTypeByDay === null || ($i < 7 && $phoneClaimTypeByDay->getGeneratedDateTime()->modify(self::LONG_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $statement = $this->entityManager->getConnection()->executeQuery(
                    $sql,
                    [
                        'startOfDay' => $startOfDay->format(self::SQL_DATE_FORMAT),
                        'endOfDay' => $endOfDay->format(self::SQL_DATE_FORMAT)
                    ]
                );

                $day = $this->addPhoneClaimTypeColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

                $phoneClaimTypeByDay = $this->upsertReport(
                    $phoneClaimTypeByDay,
                    'phoneClaimType',
                    date('D d/m/Y', $startOfDay->getTimestamp()),
                    $startOfDay,
                    $endOfDay,
                    $day,
                    $startMicroTime
                );
            }

            $byDay[$phoneClaimTypeByDay->getTitle()] = $phoneClaimTypeByDay->getData();

            $startOfDay = $startOfDay->sub(new DateInterval('P1D'));
            $endOfDay = $endOfDay->sub(new DateInterval('P1D'));
        }

        $byMonth = [];
        $startOfMonth = new DateTime('midnight first day of this month');
        $endOfMonth = (clone $startOfMonth)->add(new DateInterval('P1M'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfMonth < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $phoneClaimTypeByMonth */
            $phoneClaimTypeByMonth = $this->reportRepository->findOneBy(['type' => 'phoneClaimType', 'startDateTime' => $startOfMonth, 'endDateTime' => $endOfMonth]);
            if ($phoneClaimTypeByMonth === null || ($i < 2 && $phoneClaimTypeByMonth->getGeneratedDateTime()->modify(self::LONG_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $statement = $this->entityManager->getConnection()->executeQuery(
                    $sql,
                    [
                        'startOfDay' => $startOfMonth->format(self::SQL_DATE_FORMAT),
                        'endOfDay' => $endOfMonth->format(self::SQL_DATE_FORMAT)
                    ]
                );

                $month = $this->addPhoneClaimTypeColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

                $phoneClaimTypeByMonth = $this->upsertReport(
                    $phoneClaimTypeByMonth,
                    'phoneClaimType',
                    date('F Y', $startOfMonth->getTimestamp()),
                    $startOfMonth,
                    $endOfMonth,
                    $month,
                    $startMicroTime
                );
            }

            $byMonth[$phoneClaimTypeByMonth->getTitle()] = $phoneClaimTypeByMonth->getData();

            $startOfMonth = $startOfMonth->sub(new DateInterval('P1M'));
            $endOfMonth = $endOfMonth->sub(new DateInterval('P1M'));
        }

        return [
            'allTime' => $allTime,
            'byDay'   => $byDay,
            'byMonth' => $byMonth
        ];
    }

    private function addPhoneClaimTypeColumns(array $counts)
    {
        if (empty($counts[AssistedDigitalModel::TYPE_DONOR_DECEASED])) {
            $counts[AssistedDigitalModel::TYPE_DONOR_DECEASED] = 0;
        }
        if (empty($counts[AssistedDigitalModel::TYPE_ASSISTED_DIGITAL])) {
            $counts[AssistedDigitalModel::TYPE_ASSISTED_DIGITAL] = 0;
        }
        if (empty($counts[AssistedDigitalModel::TYPE_REFUSE_CLAIM_ONLINE])) {
            $counts[AssistedDigitalModel::TYPE_REFUSE_CLAIM_ONLINE] = 0;
        }
        if (empty($counts[AssistedDigitalModel::TYPE_DEPUTY])) {
            $counts[AssistedDigitalModel::TYPE_DEPUTY] = 0;
        }
        if (empty($counts[AssistedDigitalModel::TYPE_CHEQUE])) {
            $counts[AssistedDigitalModel::TYPE_CHEQUE] = 0;
        }
        if (empty($counts['total'])) {
            $counts['total'] = 0;
        }

        return $counts;
    }

    public function getRejectionReasonReport(DateTime $dateOfFirstClaim)
    {
        /** @var ReportEntity $rejectionReasonAllTime */
        $rejectionReasonAllTime = $this->reportRepository->findOneBy(['type' => 'rejectionReason', 'startDateTime' => $dateOfFirstClaim]);

        if ($rejectionReasonAllTime === null || $rejectionReasonAllTime->getGeneratedDateTime()->modify(self::MEDIUM_CACHE_MODIFIER) < new DateTime()) {
            //Generate stat
            $startMicroTime = microtime(true);

            $sql = 'SELECT rejection_reason, count(*) FROM claim WHERE status = \'rejected\' GROUP BY rejection_reason UNION ALL SELECT \'total\', count(*) FROM claim WHERE status = \'rejected\'';

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql
            );

            $data = $this->addRejectionReasonColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));
            $endDateTime = new DateTime();

            $rejectionReasonAllTime = $this->upsertReport(
                $rejectionReasonAllTime,
                'rejectionReason',
                'All time',
                $dateOfFirstClaim,
                $endDateTime,
                $data,
                $startMicroTime
            );
        }

        $allTime = $rejectionReasonAllTime->getData();

        return [
            'allTime' => $allTime
        ];
    }

    private function addRejectionReasonColumns(array $counts)
    {
        if (empty($counts[ClaimModel::REJECTION_REASON_NO_ELIGIBLE_POAS_FOUND])) {
            $counts[ClaimModel::REJECTION_REASON_NO_ELIGIBLE_POAS_FOUND] = 0;
        }
        if (empty($counts[ClaimModel::REJECTION_REASON_PREVIOUSLY_REFUNDED])) {
            $counts[ClaimModel::REJECTION_REASON_PREVIOUSLY_REFUNDED] = 0;
        }
        if (empty($counts[ClaimModel::REJECTION_REASON_NO_FEES_PAID])) {
            $counts[ClaimModel::REJECTION_REASON_NO_FEES_PAID] = 0;
        }
        if (empty($counts[ClaimModel::REJECTION_REASON_CLAIM_NOT_VERIFIED])) {
            $counts[ClaimModel::REJECTION_REASON_CLAIM_NOT_VERIFIED] = 0;
        }

        return $counts;
    }

    public function getDuplicateBankDetailReport(DateTime $dateOfFirstClaim)
    {
        /** @var ReportEntity $duplicateBankDetailAllTime */
        $duplicateBankDetailAllTime = $this->reportRepository->findOneBy(['type' => 'duplicateBankDetail', 'startDateTime' => $dateOfFirstClaim]);

        if ($duplicateBankDetailAllTime === null || $duplicateBankDetailAllTime->getGeneratedDateTime()->modify(self::MEDIUM_CACHE_MODIFIER) < new DateTime()) {
            //Generate stat
            $startMicroTime = microtime(true);

            $sql = 'SELECT times_used, count(*) AS frequency FROM (SELECT count(*) AS times_used FROM claim WHERE account_hash IS NOT NULL GROUP BY account_hash) AS hash_duplication GROUP BY times_used ORDER BY times_used';

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql
            );

            $data = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
            $endDateTime = new DateTime();

            $duplicateBankDetailAllTime = $this->upsertReport(
                $duplicateBankDetailAllTime,
                'duplicateBankDetail',
                'All time',
                $dateOfFirstClaim,
                $endDateTime,
                $data,
                $startMicroTime
            );
        }

        $allTime = $duplicateBankDetailAllTime->getData();

        return [
            'allTime' => $allTime
        ];
    }

    public function getRefundReport(DateTime $dateOfFirstClaim)
    {
        /** @var ReportEntity $refundAllTime */
        $refundAllTime = $this->reportRepository->findOneBy(['type' => 'refund', 'startDateTime' => $dateOfFirstClaim]);

        if ($refundAllTime === null || $refundAllTime->getGeneratedDateTime()->modify(self::MEDIUM_CACHE_MODIFIER) < new DateTime()) {
            //Generate stat
            $startMicroTime = microtime(true);

            $sql = 'SELECT \'number_of_spreadsheets\', count(DISTINCT date_trunc(\'day\', added_datetime)) FROM payment UNION ALL
                    SELECT replace(lower(method), \' \', \'_\'), count(*) FROM payment GROUP BY method UNION ALL
                    SELECT \'total_refund_amount\', SUM(amount) FROM payment UNION ALL
                    SELECT \'total\', count(*) FROM payment';

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql
            );

            $data = $this->formatRefundReport($statement->fetchAll(\PDO::FETCH_KEY_PAIR));
            $endDateTime = new DateTime();

            $refundAllTime = $this->upsertReport(
                $refundAllTime,
                'refund',
                'All time',
                $dateOfFirstClaim,
                $endDateTime,
                $data,
                $startMicroTime
            );
        }

        $allTime = $refundAllTime->getData();

        $sql = 'SELECT \'number_of_spreadsheets\', count(DISTINCT date_trunc(\'day\', added_datetime)) FROM payment WHERE added_datetime >= :startOfDay AND added_datetime <= :endOfDay UNION ALL
                SELECT replace(lower(method), \' \', \'_\'), count(*) FROM payment WHERE added_datetime >= :startOfDay AND added_datetime <= :endOfDay GROUP BY method UNION ALL
                SELECT \'total_refund_amount\', SUM(amount) FROM payment WHERE added_datetime >= :startOfDay AND added_datetime <= :endOfDay UNION ALL
                SELECT \'total\', count(*) FROM payment WHERE added_datetime >= :startOfDay AND added_datetime <= :endOfDay';

        $byDay = [];
        $startOfDay = new DateTime('today');
        $endOfDay = (clone $startOfDay)->add(new DateInterval('P1D'));
        $i = 0;
        while (count($byDay) < 45) {
            if ($endOfDay < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $refundByDay */
            $refundByDay = $this->reportRepository->findOneBy(['type' => 'refund', 'startDateTime' => $startOfDay, 'endDateTime' => $endOfDay]);
            if ($refundByDay === null || ($i < 7 && $refundByDay->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $statement = $this->entityManager->getConnection()->executeQuery(
                    $sql,
                    [
                        'startOfDay' => $startOfDay->format(self::SQL_DATE_FORMAT),
                        'endOfDay' => $endOfDay->format(self::SQL_DATE_FORMAT)
                    ]
                );

                $day = $this->formatRefundReport($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

                $refundByDay = $this->upsertReport(
                    $refundByDay,
                    'refund',
                    date('D d/m/Y', $startOfDay->getTimestamp()),
                    $startOfDay,
                    $endOfDay,
                    $day,
                    $startMicroTime
                );
            }

            $numberOfSpreadsheets = (int)$refundByDay->getData()['number_of_spreadsheets'];
            if ($numberOfSpreadsheets === 1) {
                $byDay[$refundByDay->getTitle()] = $refundByDay->getData();
            } elseif ($numberOfSpreadsheets > 0) {
                throw new \Exception("There should never be more than one spreadsheet per day. Found {$day['number_of_spreadsheets']}");
            }

            $startOfDay = $startOfDay->sub(new DateInterval('P1D'));
            $endOfDay = $endOfDay->sub(new DateInterval('P1D'));

            $i++;
        }

        $byWeek = [];
        $startOfWeek = new DateTime('last monday');
        $endOfWeek = (clone $startOfWeek)->add(new DateInterval('P1W'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfWeek < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $refundByWeek */
            $refundByWeek = $this->reportRepository->findOneBy(['type' => 'refund', 'startDateTime' => $startOfWeek, 'endDateTime' => $endOfWeek]);
            if ($refundByWeek === null || ($i < 2 && $refundByWeek->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $statement = $this->entityManager->getConnection()->executeQuery(
                    $sql,
                    [
                        'startOfDay' => $startOfWeek->format(self::SQL_DATE_FORMAT),
                        'endOfDay' => $endOfWeek->format(self::SQL_DATE_FORMAT)
                    ]
                );

                $week = $this->formatRefundReport($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

                $refundByWeek = $this->upsertReport(
                    $refundByWeek,
                    'refund',
                    date('D d/m/Y', $startOfWeek->getTimestamp()) . ' - ' . date('D d/m/Y', $endOfWeek->getTimestamp() - 1),
                    $startOfWeek,
                    $endOfWeek,
                    $week,
                    $startMicroTime
                );
            }

            $byWeek[$refundByWeek->getTitle()] = $refundByWeek->getData();

            $startOfWeek = $startOfWeek->sub(new DateInterval('P1W'));
            $endOfWeek = $endOfWeek->sub(new DateInterval('P1W'));
        }

        $byMonth = [];
        $startOfMonth = new DateTime('midnight first day of this month');
        $endOfMonth = (clone $startOfMonth)->add(new DateInterval('P1M'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfMonth < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $refundByMonth */
            $refundByMonth = $this->reportRepository->findOneBy(['type' => 'refund', 'startDateTime' => $startOfMonth, 'endDateTime' => $endOfMonth]);
            if ($refundByMonth === null || ($i < 2 && $refundByMonth->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $statement = $this->entityManager->getConnection()->executeQuery(
                    $sql,
                    [
                        'startOfDay' => $startOfMonth->format(self::SQL_DATE_FORMAT),
                        'endOfDay' => $endOfMonth->format(self::SQL_DATE_FORMAT)
                    ]
                );

                $month = $this->formatRefundReport($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

                $refundByMonth = $this->upsertReport(
                    $refundByMonth,
                    'refund',
                    date('F Y', $startOfMonth->getTimestamp()),
                    $startOfMonth,
                    $endOfMonth,
                    $month,
                    $startMicroTime
                );
            }

            $byMonth[$refundByMonth->getTitle()] = $refundByMonth->getData();

            $startOfMonth = $startOfMonth->sub(new DateInterval('P1M'));
            $endOfMonth = $endOfMonth->sub(new DateInterval('P1M'));
        }

        return [
            'allTime' => $allTime,
            'byDay'   => $byDay,
            'byWeek'  => $byWeek,
            'byMonth' => $byMonth
        ];
    }

    private function formatRefundReport(array $report)
    {
        if (empty($report['bank_transfer'])) {
            $report['bank_transfer'] = 0;
        }
        if (empty($report['cheque'])) {
            $report['cheque'] = 0;
        }
        $report['total_refund_amount'] = money_format('£%i', $report['total_refund_amount']);

        return $report;
    }

    private function getProcessingTime($dateOfFirstClaim)
    {
        $sql = 'SELECT \'mean\' AS aggregate, round(avg(EXTRACT(EPOCH FROM (c.finished_datetime - n.created_datetime)))) AS value FROM claim c JOIN note n ON n.claim_id = c.id WHERE c.finished_datetime IS NOT NULL AND n.type = \'claim_in_progress\'
                UNION ALL SELECT \'median\' AS aggregate, round(PERCENTILE_CONT(0.50) WITHIN GROUP (ORDER BY EXTRACT(EPOCH FROM (c.finished_datetime - n.created_datetime)))) AS value FROM claim c JOIN note n ON n.claim_id = c.id WHERE c.finished_datetime IS NOT NULL AND n.type = \'claim_in_progress\'
                UNION ALL SELECT \'min\' AS aggregate, (SELECT EXTRACT(EPOCH FROM (c.finished_datetime - n.created_datetime)) AS processing_time FROM claim c JOIN note n ON n.claim_id = c.id WHERE c.finished_datetime IS NOT NULL AND n.type = \'claim_in_progress\' ORDER BY processing_time ASC LIMIT 1) AS value
                UNION ALL SELECT \'max\' AS aggregate, (SELECT EXTRACT(EPOCH FROM (c.finished_datetime - n.created_datetime)) AS processing_time FROM claim c JOIN note n ON n.claim_id = c.id WHERE c.finished_datetime IS NOT NULL AND n.type = \'claim_in_progress\' ORDER BY processing_time DESC LIMIT 1) AS value';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql
        );

        $allTime = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        $sql = 'SELECT \'mean\' AS aggregate, round(avg(EXTRACT(EPOCH FROM (c.finished_datetime - n.created_datetime)))) AS value FROM claim c JOIN note n ON n.claim_id = c.id WHERE c.finished_datetime IS NOT NULL AND n.type = \'claim_in_progress\' AND finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay
                UNION ALL SELECT \'median\' AS aggregate, round(PERCENTILE_CONT(0.50) WITHIN GROUP (ORDER BY EXTRACT(EPOCH FROM (c.finished_datetime - n.created_datetime)))) AS value FROM claim c JOIN note n ON n.claim_id = c.id WHERE c.finished_datetime IS NOT NULL AND n.type = \'claim_in_progress\' AND finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay
                UNION ALL SELECT \'min\' AS aggregate, (SELECT EXTRACT(EPOCH FROM (c.finished_datetime - n.created_datetime)) AS processing_time FROM claim c JOIN note n ON n.claim_id = c.id WHERE c.finished_datetime IS NOT NULL AND n.type = \'claim_in_progress\' AND finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay ORDER BY processing_time ASC LIMIT 1) AS value
                UNION ALL SELECT \'max\' AS aggregate, (SELECT EXTRACT(EPOCH FROM (c.finished_datetime - n.created_datetime)) AS processing_time FROM claim c JOIN note n ON n.claim_id = c.id WHERE c.finished_datetime IS NOT NULL AND n.type = \'claim_in_progress\' AND finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay ORDER BY processing_time DESC LIMIT 1) AS value';

        $parameters = [];

        $byDay = [];
        $startOfDay = new DateTime('today');
        $endOfDay = (clone $startOfDay)->add(new DateInterval('P1D'));
        for ($i = 0; $i < 45; $i++) {
            if ($endOfDay < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $processingTimeByDay */
            $processingTimeByDay = $this->reportRepository->findOneBy(['type' => 'processingTime', 'startDateTime' => $startOfDay, 'endDateTime' => $endOfDay]);
            if ($processingTimeByDay === null || ($i < 7 && $processingTimeByDay->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $parameters['startOfDay'] = $startOfDay->format(self::SQL_DATE_FORMAT);
                $parameters['endOfDay'] = $endOfDay->format(self::SQL_DATE_FORMAT);

                $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

                $day = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

                $processingTimeByDay = $this->upsertReport(
                    $processingTimeByDay,
                    'processingTime',
                    date('D d/m/Y', $startOfDay->getTimestamp()),
                    $startOfDay,
                    $endOfDay,
                    $day,
                    $startMicroTime
                );
            }

            $byDay[$processingTimeByDay->getTitle()] = $processingTimeByDay->getData();

            $startOfDay = $startOfDay->sub(new DateInterval('P1D'));
            $endOfDay = $endOfDay->sub(new DateInterval('P1D'));
        }

        $byWeek = [];
        $startOfWeek = new DateTime('last monday');
        $endOfWeek = (clone $startOfWeek)->add(new DateInterval('P1W'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfWeek < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $processingTimeByWeek */
            $processingTimeByWeek = $this->reportRepository->findOneBy(['type' => 'processingTime', 'startDateTime' => $startOfWeek, 'endDateTime' => $endOfWeek]);
            if ($processingTimeByWeek === null || ($i < 2 && $processingTimeByWeek->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $parameters['startOfDay'] = $startOfWeek->format(self::SQL_DATE_FORMAT);
                $parameters['endOfDay'] = $endOfWeek->format(self::SQL_DATE_FORMAT);

                $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

                $week = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

                $processingTimeByWeek = $this->upsertReport(
                    $processingTimeByWeek,
                    'processingTime',
                    date('D d/m/Y', $startOfWeek->getTimestamp()) . ' - ' . date('D d/m/Y', $endOfWeek->getTimestamp() - 1),
                    $startOfWeek,
                    $endOfWeek,
                    $week,
                    $startMicroTime
                );
            }

            $byWeek[$processingTimeByWeek->getTitle()] = $processingTimeByWeek->getData();

            $startOfWeek = $startOfWeek->sub(new DateInterval('P1W'));
            $endOfWeek = $endOfWeek->sub(new DateInterval('P1W'));
        }

        $byMonth = [];
        $startOfMonth = new DateTime('midnight first day of this month');
        $endOfMonth = (clone $startOfMonth)->add(new DateInterval('P1M'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfMonth < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $processingTimeByMonth */
            $processingTimeByMonth = $this->reportRepository->findOneBy(['type' => 'processingTime', 'startDateTime' => $startOfMonth, 'endDateTime' => $endOfMonth]);
            if ($processingTimeByMonth === null || ($i < 2 && $processingTimeByMonth->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $parameters['startOfDay'] = $startOfMonth->format(self::SQL_DATE_FORMAT);
                $parameters['endOfDay'] = $endOfMonth->format(self::SQL_DATE_FORMAT);

                $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

                $month = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

                $processingTimeByMonth = $this->upsertReport(
                    $processingTimeByMonth,
                    'processingTime',
                    date('F Y', $startOfMonth->getTimestamp()),
                    $startOfMonth,
                    $endOfMonth,
                    $month,
                    $startMicroTime
                );
            }

            $byMonth[$processingTimeByMonth->getTitle()] = $processingTimeByMonth->getData();

            $startOfMonth = $startOfMonth->sub(new DateInterval('P1M'));
            $endOfMonth = $endOfMonth->sub(new DateInterval('P1M'));
        }

        return [
            'allTime' => $allTime,
            'byDay'   => $byDay,
            'byWeek'  => $byWeek,
            'byMonth' => $byMonth
        ];
    }

    private function getCompletionTime($dateOfFirstClaim)
    {
        $sql = 'SELECT \'mean\' AS aggregate, round(avg(EXTRACT(EPOCH FROM (finished_datetime - received_datetime)))) AS value FROM claim WHERE finished_datetime IS NOT NULL
                UNION ALL SELECT \'median\' AS aggregate, round(PERCENTILE_CONT(0.50) WITHIN GROUP (ORDER BY EXTRACT(EPOCH FROM (finished_datetime - received_datetime)))) AS value FROM claim WHERE finished_datetime IS NOT NULL
                UNION ALL SELECT \'min\' AS aggregate, (SELECT EXTRACT(EPOCH FROM (finished_datetime - received_datetime)) AS processing_time FROM claim WHERE finished_datetime IS NOT NULL ORDER BY processing_time ASC LIMIT 1) AS value
                UNION ALL SELECT \'max\' AS aggregate, (SELECT EXTRACT(EPOCH FROM (finished_datetime - received_datetime)) AS processing_time FROM claim WHERE finished_datetime IS NOT NULL ORDER BY processing_time DESC LIMIT 1) AS value';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql
        );

        $allTime = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        $sql = 'SELECT \'mean\' AS aggregate, round(avg(EXTRACT(EPOCH FROM (finished_datetime - received_datetime)))) AS value FROM claim WHERE finished_datetime IS NOT NULL AND finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay
                UNION ALL SELECT \'median\' AS aggregate, round(PERCENTILE_CONT(0.50) WITHIN GROUP (ORDER BY EXTRACT(EPOCH FROM (finished_datetime - received_datetime)))) AS value FROM claim WHERE finished_datetime IS NOT NULL AND finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay
                UNION ALL SELECT \'min\' AS aggregate, (SELECT EXTRACT(EPOCH FROM (finished_datetime - received_datetime)) AS processing_time FROM claim WHERE finished_datetime IS NOT NULL AND finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay ORDER BY processing_time ASC LIMIT 1) AS value
                UNION ALL SELECT \'max\' AS aggregate, (SELECT EXTRACT(EPOCH FROM (finished_datetime - received_datetime)) AS processing_time FROM claim WHERE finished_datetime IS NOT NULL AND finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay ORDER BY processing_time DESC LIMIT 1) AS value';

        $parameters = [];

        $byDay = [];
        $startOfDay = new DateTime('today');
        $endOfDay = (clone $startOfDay)->add(new DateInterval('P1D'));
        for ($i = 0; $i < 45; $i++) {
            if ($endOfDay < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $completionTimeByDay */
            $completionTimeByDay = $this->reportRepository->findOneBy(['type' => 'completionTime', 'startDateTime' => $startOfDay, 'endDateTime' => $endOfDay]);
            if ($completionTimeByDay === null || ($i < 7 && $completionTimeByDay->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $parameters['startOfDay'] = $startOfDay->format(self::SQL_DATE_FORMAT);
                $parameters['endOfDay'] = $endOfDay->format(self::SQL_DATE_FORMAT);

                $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

                $day = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

                $completionTimeByDay = $this->upsertReport(
                    $completionTimeByDay,
                    'completionTime',
                    date('D d/m/Y', $startOfDay->getTimestamp()),
                    $startOfDay,
                    $endOfDay,
                    $day,
                    $startMicroTime
                );
            }

            $byDay[$completionTimeByDay->getTitle()] = $completionTimeByDay->getData();

            $startOfDay = $startOfDay->sub(new DateInterval('P1D'));
            $endOfDay = $endOfDay->sub(new DateInterval('P1D'));
        }

        $byWeek = [];
        $startOfWeek = new DateTime('last monday');
        $endOfWeek = (clone $startOfWeek)->add(new DateInterval('P1W'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfWeek < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $completionTimeByWeek */
            $completionTimeByWeek = $this->reportRepository->findOneBy(['type' => 'completionTime', 'startDateTime' => $startOfWeek, 'endDateTime' => $endOfWeek]);
            if ($completionTimeByWeek === null || ($i < 2 && $completionTimeByWeek->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $parameters['startOfDay'] = $startOfWeek->format(self::SQL_DATE_FORMAT);
                $parameters['endOfDay'] = $endOfWeek->format(self::SQL_DATE_FORMAT);

                $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

                $week = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

                $completionTimeByWeek = $this->upsertReport(
                    $completionTimeByWeek,
                    'completionTime',
                    date('D d/m/Y', $startOfWeek->getTimestamp()) . ' - ' . date('D d/m/Y', $endOfWeek->getTimestamp() - 1),
                    $startOfWeek,
                    $endOfWeek,
                    $week,
                    $startMicroTime
                );
            }

            $byWeek[$completionTimeByWeek->getTitle()] = $completionTimeByWeek->getData();

            $startOfWeek = $startOfWeek->sub(new DateInterval('P1W'));
            $endOfWeek = $endOfWeek->sub(new DateInterval('P1W'));
        }

        $byMonth = [];
        $startOfMonth = new DateTime('midnight first day of this month');
        $endOfMonth = (clone $startOfMonth)->add(new DateInterval('P1M'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfMonth < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $completionTimeByMonth */
            $completionTimeByMonth = $this->reportRepository->findOneBy(['type' => 'completionTime', 'startDateTime' => $startOfMonth, 'endDateTime' => $endOfMonth]);
            if ($completionTimeByMonth === null || ($i < 2 && $completionTimeByMonth->getGeneratedDateTime()->modify(self::SHORT_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $parameters['startOfDay'] = $startOfMonth->format(self::SQL_DATE_FORMAT);
                $parameters['endOfDay'] = $endOfMonth->format(self::SQL_DATE_FORMAT);

                $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

                $month = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

                $completionTimeByMonth = $this->upsertReport(
                    $completionTimeByMonth,
                    'completionTime',
                    date('F Y', $startOfMonth->getTimestamp()),
                    $startOfMonth,
                    $endOfMonth,
                    $month,
                    $startMicroTime
                );
            }

            $byMonth[$completionTimeByMonth->getTitle()] = $completionTimeByMonth->getData();

            $startOfMonth = $startOfMonth->sub(new DateInterval('P1M'));
            $endOfMonth = $endOfMonth->sub(new DateInterval('P1M'));
        }

        return [
            'allTime' => $allTime,
            'byDay'   => $byDay,
            'byWeek'  => $byWeek,
            'byMonth' => $byMonth
        ];
    }

    private function getNotifications($dateOfFirstClaim)
    {
        /** @var ReportEntity $notifyAllTime */
        $notifyAllTime = $this->reportRepository->findOneBy(['type' => 'notify', 'startDateTime' => $dateOfFirstClaim]);

        if ($notifyAllTime === null || $notifyAllTime->getGeneratedDateTime()->modify(self::MEDIUM_CACHE_MODIFIER) < new DateTime()) {
            //Generate stat
            $startMicroTime = microtime(true);

            $sql = 'SELECT \'notifications_opt_out\', count(*) FROM claim WHERE ((json_data->>\'contact\')::JSON->>\'receive-notifications\')::BOOLEAN IS FALSE UNION ALL
                    SELECT \'outcome_email_sent\', count(*) FROM claim WHERE outcome_email_sent IS TRUE UNION ALL
                    SELECT \'outcome_text_sent\', count(*) FROM claim WHERE outcome_text_sent IS TRUE UNION ALL
                    SELECT \'outcome_letter_sent\', count(*) FROM claim WHERE outcome_letter_sent IS TRUE UNION ALL
                    SELECT \'outcome_phone_called\', count(*) FROM claim WHERE outcome_phone_called IS TRUE';

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql
            );

            $data = $this->addStatusColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));
            $endDateTime = new DateTime();

            $notifyAllTime = $this->upsertReport(
                $notifyAllTime,
                'notify',
                'All time',
                $dateOfFirstClaim,
                $endDateTime,
                $data,
                $startMicroTime
            );
        }

        $allTime = $notifyAllTime->getData();

        $sql = 'SELECT \'notifications_opt_out\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay AND ((json_data->>\'contact\')::JSON->>\'receive-notifications\')::BOOLEAN IS FALSE UNION ALL
                SELECT \'outcome_email_sent\', count(*) FROM claim WHERE finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay AND outcome_email_sent IS TRUE UNION ALL
                SELECT \'outcome_text_sent\', count(*) FROM claim WHERE finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay AND outcome_text_sent IS TRUE UNION ALL
                SELECT \'outcome_letter_sent\', count(*) FROM claim WHERE finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay AND outcome_letter_sent IS TRUE UNION ALL
                SELECT \'outcome_phone_called\', count(*) FROM claim WHERE finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay AND outcome_phone_called IS TRUE';

        $byDay = [];
        $startOfDay = new DateTime('today');
        $endOfDay = (clone $startOfDay)->add(new DateInterval('P1D'));
        for ($i = 0; $i < 45; $i++) {
            if ($endOfDay < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $notifyByDay */
            $notifyByDay = $this->reportRepository->findOneBy(['type' => 'notify', 'startDateTime' => $startOfDay, 'endDateTime' => $endOfDay]);
            if ($notifyByDay === null || ($i < 7 && $notifyByDay->getGeneratedDateTime()->modify(self::MEDIUM_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $statement = $this->entityManager->getConnection()->executeQuery(
                    $sql,
                    [
                        'startOfDay' => $startOfDay->format(self::SQL_DATE_FORMAT),
                        'endOfDay' => $endOfDay->format(self::SQL_DATE_FORMAT)
                    ]
                );

                $day = $this->addPhoneClaimTypeColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

                $notifyByDay = $this->upsertReport(
                    $notifyByDay,
                    'notify',
                    date('D d/m/Y', $startOfDay->getTimestamp()),
                    $startOfDay,
                    $endOfDay,
                    $day,
                    $startMicroTime
                );
            }

            $byDay[$notifyByDay->getTitle()] = $notifyByDay->getData();

            $startOfDay = $startOfDay->sub(new DateInterval('P1D'));
            $endOfDay = $endOfDay->sub(new DateInterval('P1D'));
        }

        $byMonth = [];
        $startOfMonth = new DateTime('midnight first day of this month');
        $endOfMonth = (clone $startOfMonth)->add(new DateInterval('P1M'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfMonth < $dateOfFirstClaim) {
                break;
            }

            /** @var ReportEntity $notifyByMonth */
            $notifyByMonth = $this->reportRepository->findOneBy(['type' => 'notify', 'startDateTime' => $startOfMonth, 'endDateTime' => $endOfMonth]);
            if ($notifyByMonth === null || ($i < 2 && $notifyByMonth->getGeneratedDateTime()->modify(self::MEDIUM_CACHE_MODIFIER) < new DateTime())) {
                //Generate stat
                $startMicroTime = microtime(true);

                $statement = $this->entityManager->getConnection()->executeQuery(
                    $sql,
                    [
                        'startOfDay' => $startOfMonth->format(self::SQL_DATE_FORMAT),
                        'endOfDay' => $endOfMonth->format(self::SQL_DATE_FORMAT)
                    ]
                );

                $month = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

                $notifyByMonth = $this->upsertReport(
                    $notifyByMonth,
                    'notify',
                    date('F Y', $startOfMonth->getTimestamp()),
                    $startOfMonth,
                    $endOfMonth,
                    $month,
                    $startMicroTime
                );
            }

            $byMonth[$notifyByMonth->getTitle()] = $notifyByMonth->getData();

            $startOfMonth = $startOfMonth->sub(new DateInterval('P1M'));
            $endOfMonth = $endOfMonth->sub(new DateInterval('P1M'));
        }

        return [
            'allTime' => $allTime,
            'byDay'   => $byDay,
            'byMonth' => $byMonth
        ];
    }

    private function getPoasPerClaim($dateOfFirstClaim)
    {
        /** @var ReportEntity $poasPerClaimAllTime */
        $poasPerClaimAllTime = $this->reportRepository->findOneBy(['type' => 'poasPerClaim', 'startDateTime' => $dateOfFirstClaim]);

        if ($poasPerClaimAllTime === null || $poasPerClaimAllTime->getGeneratedDateTime()->modify(self::MEDIUM_CACHE_MODIFIER) < new DateTime()) {
            //Generate stat
            $startMicroTime = microtime(true);

            $sql = 'SELECT poas_per_claim, count(*) AS frequency FROM (SELECT count(*) as poas_per_claim FROM poa GROUP BY claim_id) AS poas_per_claim GROUP BY poas_per_claim ORDER BY poas_per_claim';

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql
            );

            $data = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
            $endDateTime = new DateTime();

            $poasPerClaimAllTime = $this->upsertReport(
                $poasPerClaimAllTime,
                'poasPerClaim',
                'All time',
                $dateOfFirstClaim,
                $endDateTime,
                $data,
                $startMicroTime
            );
        }

        $allTime = $poasPerClaimAllTime->getData();

        return [
            'allTime' => $allTime
        ];
    }

    /**
     * @param ReportEntity $report
     * @param string $type
     * @param string $title
     * @param DateTime $startDateTime
     * @param DateTime $endDateTime
     * @param array $data
     * @param float $startMicroTime
     * @return ReportEntity
     */
    private function upsertReport(
        $report,
        string $type,
        string $title,
        DateTime $startDateTime,
        DateTime $endDateTime,
        array $data,
        float $startMicroTime
    ) {
        $generated = new DateTime();
        $generationTimeInMs = round((microtime(true) - $startMicroTime) * 1000);

        if ($report === null) {
            //Persist report
            $report = new ReportEntity($type, $title, $startDateTime, $endDateTime, $data, $generationTimeInMs);
            $this->entityManager->persist($report);
        } else {
            $report->setStartDateTime($startDateTime);
            $report->setEndDateTime($endDateTime);
            $report->setData($data);
            $report->setGeneratedDateTime($generated);
            $report->setGenerationTimeInMs($generationTimeInMs);
        }

        return $report;
    }
}
