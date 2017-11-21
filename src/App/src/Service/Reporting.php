<?php

namespace App\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use Ingestion\Service\ApplicationIngestion;

/**
 * Class Reporting
 * @package App\Service
 */
class Reporting
{
    /**
     * @var EntityManager
     */
    private $claimsEntityManager;
    /**
     * @var ApplicationIngestion
     */
    private $applicationIngestionService;

    public function __construct(EntityManager $claimsEntityManager, ApplicationIngestion $applicationIngestionService)
    {
        $this->claimsEntityManager = $claimsEntityManager;
        $this->applicationIngestionService = $applicationIngestionService;
    }

    public function getAllReports()
    {
        //TODO: Get proper migration running via cron job
        $this->applicationIngestionService->ingestAllApplication();

        $generated = new DateTime();

        return [
            'generated' => date('d/m/Y H:i:s', $generated->getTimestamp())
        ];
    }

    public function getClaimReport()
    {

    }

    public function getClaimSourceReport()
    {

    }

    public function getRejectionReasonReport()
    {

    }

    public function getDuplicateBankDetailReport()
    {

    }

    public function getRefundsReport()
    {

    }
}