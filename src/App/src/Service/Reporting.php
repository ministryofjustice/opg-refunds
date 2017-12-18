<?php

namespace App\Service;

use Opg\Refunds\Caseworker\DataModel\Applications\AssistedDigital as AssistedDigitalModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Ingestion\Service\ApplicationIngestion;

/**
 * Class Reporting
 * @package App\Service
 */
class Reporting
{
    const GENERATED_DATE_FORMAT = 'd/m/Y H:i:s';
    const SQL_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ApplicationIngestion
     */
    private $applicationIngestionService;

    public function __construct(EntityManager $claimsEntityManager, ApplicationIngestion $applicationIngestionService)
    {
        $this->entityManager = $claimsEntityManager;
        $this->applicationIngestionService = $applicationIngestionService;
    }

    public function getAllReports()
    {
        //TODO: Get proper migration running via cron job
        $this->applicationIngestionService->ingestAllApplication();

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
            'poasPerClaim' => $this->getPoasPerClaim($dateOfFirstClaim),
        ];

        $end = microtime(true);

        $reports['generated'] = date('d/m/Y H:i:s', (new DateTime())->getTimestamp());
        $reports['generationTimeInMs'] = round(($end - $start) * 1000);

        return $reports;
    }

    public function getClaimReport(DateTime $dateOfFirstClaim)
    {
        $sql = 'SELECT status, count(*) FROM claim GROUP BY status UNION ALL
                SELECT \'total\', count(*) FROM claim UNION ALL
                SELECT \'outcome_changed\', COUNT(*) FROM note WHERE type = \'claim_outcome_changed\'';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql
        );

        $allTime = $this->addStatusColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

        $sql = 'SELECT status, count(*) FROM claim WHERE status = :statusPending AND received_datetime >= :startOfDay AND received_datetime <= :endOfDay GROUP BY status UNION ALL
                SELECT status, count(*) FROM claim WHERE status = :statusInProgress AND updated_datetime >= :startOfDay AND updated_datetime <= :endOfDay GROUP BY status UNION ALL
                SELECT status, count(*) FROM claim WHERE status IN (:statusDuplicate, :statusRejected, :statusAccepted) AND finished_datetime >= :startOfDay AND finished_datetime <= :endOfDay GROUP BY status UNION ALL
                SELECT \'total\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay UNION ALL
                SELECT \'outcome_changed\', COUNT(*) FROM claim c JOIN note n ON c.id = n.claim_id WHERE n.created_datetime >= :startOfDay AND n.created_datetime <= :endOfDay AND n.type = \'claim_outcome_changed\'';

        $parameters = [
            'statusPending' => ClaimModel::STATUS_PENDING,
            'statusInProgress' => ClaimModel::STATUS_IN_PROGRESS,
            'statusDuplicate' => ClaimModel::STATUS_DUPLICATE,
            'statusRejected' => ClaimModel::STATUS_REJECTED,
            'statusAccepted' => ClaimModel::STATUS_ACCEPTED
        ];

        $byDay = [];
        $startOfDay = new DateTime('today');
        $endOfDay = (clone $startOfDay)->add(new DateInterval('P1D'));
        for ($i = 0; $i < 45; $i++) {
            if ($endOfDay < $dateOfFirstClaim) {
                break;
            }

            $parameters['startOfDay'] = $startOfDay->format(self::SQL_DATE_FORMAT);
            $parameters['endOfDay'] = $endOfDay->format(self::SQL_DATE_FORMAT);

            $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

            $day = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byDay[date('D d/m/Y', $startOfDay->getTimestamp())] = $this->addStatusColumns($day);

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

            $parameters['startOfDay'] = $startOfWeek->format(self::SQL_DATE_FORMAT);
            $parameters['endOfDay'] = $endOfWeek->format(self::SQL_DATE_FORMAT);

            $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

            $week = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byWeek[date('D d/m/Y', $startOfWeek->getTimestamp()) . ' - ' . date('D d/m/Y', $endOfWeek->getTimestamp() - 1)] = $this->addStatusColumns($week);

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

            $parameters['startOfDay'] = $startOfMonth->format(self::SQL_DATE_FORMAT);
            $parameters['endOfDay'] = $endOfMonth->format(self::SQL_DATE_FORMAT);

            $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

            $month = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byMonth[date('F Y', $startOfMonth->getTimestamp())] = $this->addStatusColumns($month);

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
        $sql = 'SELECT \'donor\', count(*) FROM claim WHERE json_data->>\'applicant\' = \'donor\' UNION ALL
                SELECT \'attorney\', count(*) FROM claim WHERE json_data->>\'applicant\' = \'attorney\' UNION ALL
                SELECT \'assisted_digital\', count(*) FROM claim WHERE json_data->\'ad\' IS NOT NULL UNION ALL
                SELECT \'donor_deceased\', count(*) FROM claim WHERE json_data->>\'deceased\' = \'true\' UNION ALL
                SELECT \'total\', count(*) FROM claim';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql
        );

        $allTime = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        $sql = 'SELECT \'donor\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay AND json_data->>\'applicant\' = \'donor\' UNION ALL
                SELECT \'attorney\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay AND json_data->>\'applicant\' = \'attorney\' UNION ALL
                SELECT \'assisted_digital\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay AND json_data->\'ad\' IS NOT NULL UNION ALL
                SELECT \'donor_deceased\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay AND json_data->>\'deceased\' = \'true\' UNION ALL
                SELECT \'total\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay';

        $byMonth = [];
        $startOfMonth = new DateTime('midnight first day of this month');
        $endOfMonth = (clone $startOfMonth)->add(new DateInterval('P1M'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfMonth < $dateOfFirstClaim) {
                break;
            }

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql,
                [
                    'startOfDay' => $startOfMonth->format(self::SQL_DATE_FORMAT),
                    'endOfDay' => $endOfMonth->format(self::SQL_DATE_FORMAT)
                ]
            );

            $month = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byMonth[date('F Y', $startOfMonth->getTimestamp())] = $month;

            $startOfMonth = $startOfMonth->sub(new DateInterval('P1M'));
            $endOfMonth = $endOfMonth->sub(new DateInterval('P1M'));
        }

        return [
            'allTime' => $allTime,
            'byMonth' => $byMonth
        ];
    }

    public function getPhoneClaimTypeReport(DateTime $dateOfFirstClaim)
    {
        $sql = 'SELECT json_data->\'ad\'->\'meta\'->>\'type\' AS type, count(*) FROM claim WHERE json_data->\'ad\'->\'meta\'->\'type\' IS NOT NULL GROUP BY type
                UNION ALL SELECT \'total\', count(*) FROM claim WHERE json_data->\'ad\'->\'meta\'->\'type\' IS NOT NULL';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql
        );

        $allTime = $this->addPhoneClaimTypeColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

        $sql = 'SELECT json_data->\'ad\'->\'meta\'->>\'type\' AS type, count(*) FROM claim WHERE json_data->\'ad\'->\'meta\'->\'type\' IS NOT NULL AND received_datetime >= :startOfDay AND received_datetime <= :endOfDay GROUP BY type
                UNION ALL SELECT \'total\', count(*) FROM claim WHERE json_data->\'ad\'->\'meta\'->\'type\' IS NOT NULL AND received_datetime >= :startOfDay AND received_datetime <= :endOfDay';

        $byMonth = [];
        $startOfMonth = new DateTime('midnight first day of this month');
        $endOfMonth = (clone $startOfMonth)->add(new DateInterval('P1M'));
        for ($i = 0; $i < 12; $i++) {
            if ($endOfMonth < $dateOfFirstClaim) {
                break;
            }

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql,
                [
                    'startOfDay' => $startOfMonth->format(self::SQL_DATE_FORMAT),
                    'endOfDay' => $endOfMonth->format(self::SQL_DATE_FORMAT)
                ]
            );

            $month = $this->addPhoneClaimTypeColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

            $byMonth[date('F Y', $startOfMonth->getTimestamp())] = $month;

            $startOfMonth = $startOfMonth->sub(new DateInterval('P1M'));
            $endOfMonth = $endOfMonth->sub(new DateInterval('P1M'));
        }

        return [
            'allTime' => $allTime,
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
        $sql = 'SELECT rejection_reason, count(*) FROM claim WHERE status = \'rejected\' GROUP BY rejection_reason UNION ALL SELECT \'total\', count(*) FROM claim';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql
        );

        $allTime = $this->addRejectionReasonColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

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
        $sql = 'SELECT times_used, count(*) AS frequency FROM (SELECT count(*) AS times_used FROM claim WHERE account_hash IS NOT NULL GROUP BY account_hash) AS hash_duplication GROUP BY times_used ORDER BY times_used';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql
        );

        $allTime = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        return [
            'allTime' => $allTime
        ];
    }

    public function getRefundReport(DateTime $dateOfFirstClaim)
    {
        $sql = 'SELECT \'number_of_spreadsheets\', count(DISTINCT date_trunc(\'day\', added_datetime)) FROM payment UNION ALL
                SELECT replace(lower(method), \' \', \'_\'), count(*) FROM payment GROUP BY method UNION ALL
                SELECT \'total_refund_amount\', SUM(amount) FROM payment UNION ALL
                SELECT \'total\', count(*) FROM payment';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql
        );

        $allTime = $this->formatRefundReport($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

        $sql = 'SELECT \'number_of_spreadsheets\', count(DISTINCT date_trunc(\'day\', added_datetime)) FROM payment WHERE added_datetime >= :startOfDay AND added_datetime <= :endOfDay UNION ALL
                SELECT replace(lower(method), \' \', \'_\'), count(*) FROM payment WHERE added_datetime >= :startOfDay AND added_datetime <= :endOfDay GROUP BY method UNION ALL
                SELECT \'total_refund_amount\', SUM(amount) FROM payment WHERE added_datetime >= :startOfDay AND added_datetime <= :endOfDay UNION ALL
                SELECT \'total\', count(*) FROM payment WHERE added_datetime >= :startOfDay AND added_datetime <= :endOfDay';

        $byDay = [];
        $startOfDay = new DateTime('today');
        $endOfDay = (clone $startOfDay)->add(new DateInterval('P1D'));
        while (count($byDay) < 45) {
            if ($endOfDay < $dateOfFirstClaim) {
                break;
            }

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql,
                [
                    'startOfDay' => $startOfDay->format(self::SQL_DATE_FORMAT),
                    'endOfDay' => $endOfDay->format(self::SQL_DATE_FORMAT)
                ]
            );

            $day = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            if ((int)$day['number_of_spreadsheets'] === 1) {
                $byDay[date('D d/m/Y', $startOfDay->getTimestamp())] = $this->formatRefundReport($day);
            } elseif ((int)$day['number_of_spreadsheets'] > 0) {
                throw new \Exception("There should never be more than one spreadsheet per day. Found {$day['number_of_spreadsheets']}");
            }

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

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql,
                [
                    'startOfDay' => $startOfWeek->format(self::SQL_DATE_FORMAT),
                    'endOfDay' => $endOfWeek->format(self::SQL_DATE_FORMAT)
                ]
            );

            $week = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byWeek[date('D d/m/Y', $startOfWeek->getTimestamp()) . ' - ' . date('D d/m/Y', $endOfWeek->getTimestamp() - 1)] = $this->formatRefundReport($week);

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

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql,
                [
                    'startOfDay' => $startOfMonth->format(self::SQL_DATE_FORMAT),
                    'endOfDay' => $endOfMonth->format(self::SQL_DATE_FORMAT)
                ]
            );

            $month = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byMonth[date('F Y', $startOfMonth->getTimestamp())] = $this->formatRefundReport($month);

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

            $parameters['startOfDay'] = $startOfDay->format(self::SQL_DATE_FORMAT);
            $parameters['endOfDay'] = $endOfDay->format(self::SQL_DATE_FORMAT);

            $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

            $day = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byDay[date('D d/m/Y', $startOfDay->getTimestamp())] = $day;

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

            $parameters['startOfDay'] = $startOfWeek->format(self::SQL_DATE_FORMAT);
            $parameters['endOfDay'] = $endOfWeek->format(self::SQL_DATE_FORMAT);

            $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

            $week = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byWeek[date('D d/m/Y', $startOfWeek->getTimestamp()) . ' - ' . date('D d/m/Y', $endOfWeek->getTimestamp() - 1)] = $week;

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

            $parameters['startOfDay'] = $startOfMonth->format(self::SQL_DATE_FORMAT);
            $parameters['endOfDay'] = $endOfMonth->format(self::SQL_DATE_FORMAT);

            $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

            $month = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byMonth[date('F Y', $startOfMonth->getTimestamp())] = $month;

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

            $parameters['startOfDay'] = $startOfDay->format(self::SQL_DATE_FORMAT);
            $parameters['endOfDay'] = $endOfDay->format(self::SQL_DATE_FORMAT);

            $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

            $day = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byDay[date('D d/m/Y', $startOfDay->getTimestamp())] = $day;

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

            $parameters['startOfDay'] = $startOfWeek->format(self::SQL_DATE_FORMAT);
            $parameters['endOfDay'] = $endOfWeek->format(self::SQL_DATE_FORMAT);

            $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

            $week = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byWeek[date('D d/m/Y', $startOfWeek->getTimestamp()) . ' - ' . date('D d/m/Y', $endOfWeek->getTimestamp() - 1)] = $week;

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

            $parameters['startOfDay'] = $startOfMonth->format(self::SQL_DATE_FORMAT);
            $parameters['endOfDay'] = $endOfMonth->format(self::SQL_DATE_FORMAT);

            $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

            $month = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            $byMonth[date('F Y', $startOfMonth->getTimestamp())] = $month;

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

    private function getPoasPerClaim($dateOfFirstClaim)
    {
        $sql = 'SELECT poas_per_claim, count(*) AS frequency FROM (SELECT count(*) as poas_per_claim FROM poa GROUP BY claim_id) AS poas_per_claim GROUP BY poas_per_claim ORDER BY poas_per_claim';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql
        );

        $allTime = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        return [
            'allTime' => $allTime
        ];
    }
}
