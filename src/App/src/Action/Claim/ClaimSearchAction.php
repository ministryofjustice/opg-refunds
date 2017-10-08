<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use App\Action\AbstractAction;
use Fig\Http\Message\RequestMethodInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ClaimSearchAction
 * @package App\Action\Claim
 */
class ClaimSearchAction extends AbstractAction
{
    /**
     * @var ClaimService
     */
    protected $claimService;

    /**
     * AbstractClaimAction constructor
     * @param ClaimService $claimService
     */
    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ($request->getMethod() === RequestMethodInterface::METHOD_POST) {
            //  Search
        }

        $queryParameters = $request->getQueryParams();

        $page = isset($queryParameters['page']) ? $queryParameters['page'] : null;
        $pageSize = isset($queryParameters['pageSize']) ? $queryParameters['pageSize'] : null;
        $donorName = isset($queryParameters['donorName']) ? $queryParameters['donorName'] : null;
        $assignedToId = isset($queryParameters['assignedToId']) ? $queryParameters['assignedToId'] : null;
        $status = isset($queryParameters['status']) ? $queryParameters['status'] : null;

        $claimSummaryPage = $this->claimService->searchClaims($page, $pageSize, $donorName, $assignedToId, $status);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-search-page', [
            'claimSummaryPage' => $claimSummaryPage
        ]));
    }
}