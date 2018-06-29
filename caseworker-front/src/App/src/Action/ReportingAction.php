<?php

namespace App\Action;

use App\Service\Reporting\Reporting as ReportingService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

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
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $reports = $this->reportingService->getAllReports();

        return new HtmlResponse($this->getTemplateRenderer()->render('app::reporting-page', [
            'reports' => $reports
        ]));
    }
}
