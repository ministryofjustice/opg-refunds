<?php

namespace App\Action;

use App\Service\Reporting\Reporting as ReportingService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\HtmlResponse;

/**
 * Class ReportingAction
 * @package App\Action
 */
class ReportingAction extends AbstractAction
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
     * @return HtmlResponse
     */
    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        $reports = $this->reportingService->getAllReports();

        return new HtmlResponse($this->getTemplateRenderer()->render('app::reporting-page', [
            'reports' => $reports
        ]));
    }
}
