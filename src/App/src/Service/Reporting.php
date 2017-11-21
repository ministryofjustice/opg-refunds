<?php

namespace App\Service;

use DateInterval;
use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Ingestion\Service\ApplicationIngestion;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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

        $dateOfFirstClaim = new DateTime($this->entityManager->getConnection()->executeQuery(
            'SELECT received_datetime FROM claim ORDER BY received_datetime LIMIT 1'
        )->fetch()['received_datetime']);

        $generated = new DateTime();

        return [
            'generated' => date('d/m/Y H:i:s', $generated->getTimestamp()),
            'claim' => $this->getClaimReport($dateOfFirstClaim),
            'claimSource' => $this->getClaimSourceReport($dateOfFirstClaim),
            'rejectionReason' => $this->getRejectionReasonReport($dateOfFirstClaim),
            'duplicateBankDetail' => $this->getDuplicateBankDetailReport($dateOfFirstClaim),
            'refund' => $this->getRefundReport($dateOfFirstClaim),
        ];
    }

    public function getClaimReport(DateTime $dateOfFirstClaim)
    {
        $sql = 'SELECT status, count(*) FROM claim GROUP BY status UNION ALL SELECT \'total\', count(*) FROM claim';

        $statement = $this->entityManager->getConnection()->executeQuery(
            $sql
        );

        $allTime = $this->addStatusColumns($statement->fetchAll(\PDO::FETCH_KEY_PAIR));

        $sql = 'SELECT status, count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay GROUP BY status UNION ALL SELECT \'total\', count(*) FROM claim WHERE received_datetime >= :startOfDay AND received_datetime <= :endOfDay';

        $byDay = [];
        $startOfDay = new DateTime('today');
        $endOfDay = (clone $startOfDay)->add(new DateInterval('P1D'));
        for ($i = 0; $i < 30; $i++) {
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

            $byDay[date('D d/m/Y', $startOfDay->getTimestamp())] = $this->addStatusColumns($day);

            $startOfDay = $startOfDay->sub(new DateInterval('P1D'));
            $endOfDay = $endOfDay->sub(new DateInterval('P1D'));
        }

        $byWeek = [];
        $startOfWeek = new DateTime('last sunday');
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

            $byWeek[date('D d/m/Y', $startOfWeek->getTimestamp()) . ' - ' . date('D d/m/Y', $endOfWeek->getTimestamp())] = $this->addStatusColumns($week);

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

    private function addStatusColumns(array $statusCounts)
    {
        if (empty($statusCounts['pending'])) {
            $statusCounts['pending'] = 0;
        }
        if (empty($statusCounts['in_progress'])) {
            $statusCounts['in_progress'] = 0;
        }
        if (empty($statusCounts['rejected'])) {
            $statusCounts['rejected'] = 0;
        }
        if (empty($statusCounts['accepted'])) {
            $statusCounts['accepted'] = 0;
        }
        if (empty($statusCounts['total'])) {
            $statusCounts['total'] = 0;
        }

        return $statusCounts;
    }

    public function getClaimSourceReport(DateTime $dateOfFirstClaim)
    {
        return [];
    }

    public function getRejectionReasonReport(DateTime $dateOfFirstClaim)
    {
        return [];
    }

    public function getDuplicateBankDetailReport(DateTime $dateOfFirstClaim)
    {
        return [];
    }

    public function getRefundReport(DateTime $dateOfFirstClaim)
    {
        return [];
    }
}