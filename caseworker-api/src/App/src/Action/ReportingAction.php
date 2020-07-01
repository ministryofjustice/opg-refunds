<?php

namespace App\Action;

use App\Service\Reporting as ReportingService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;

/**
 * Class ReportingAction
 * @package App\Action
 */
class ReportingAction extends AbstractRestfulAction
{
    /**
     * @var ReportingService
     */
    private $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface|Response
     */
    public function indexAction(ServerRequestInterface $request)
    {
        $reports = $this->reportingService->getAllReports();

        return new JsonResponse($reports);
    }
}
