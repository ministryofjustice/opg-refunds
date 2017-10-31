<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use App\Service\User as UserService;
use Exception;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class ClaimSearchAction
 * @package App\Action
 */
class ClaimSearchAction extends AbstractRestfulAction
{
    /**
     * @var ClaimService
     */
    private $claimService;

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * READ/GET index action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $queryParameters = $request->getQueryParams();

        $page = isset($queryParameters['page']) ? $queryParameters['page'] : null;
        $pageSize = isset($queryParameters['pageSize']) ? $queryParameters['pageSize'] : null;
        $donorName = isset($queryParameters['donorName']) ? $queryParameters['donorName'] : null;
        $assignedToId = isset($queryParameters['assignedToId']) ? $queryParameters['assignedToId'] : null;
        $status = isset($queryParameters['status']) ? $queryParameters['status'] : null;
        $accountHash = isset($queryParameters['accountHash']) ? $queryParameters['accountHash'] : null;
        $orderBy = isset($queryParameters['orderBy']) ? $queryParameters['orderBy'] : null;
        $sort = isset($queryParameters['sort']) ? $queryParameters['sort'] : null;

        //  Search claims
        $claimSummaryPage = $this->claimService->search($page, $pageSize, $donorName, $assignedToId, $status, $accountHash, $orderBy, $sort);

        return new JsonResponse($claimSummaryPage->getArrayCopy());
    }
}