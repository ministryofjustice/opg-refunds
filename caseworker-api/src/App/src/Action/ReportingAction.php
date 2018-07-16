<?php

namespace App\Action;

use App\Service\Reporting as ReportingService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\JsonResponse;

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
     * @param DelegateInterface $delegate
     * @return ResponseInterface|Response
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $reports = $this->reportingService->getAllReports();

        return new JsonResponse($reports);
    }
}
