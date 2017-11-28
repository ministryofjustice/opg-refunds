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
        $search = isset($queryParameters['search']) ? $queryParameters['search'] : null;
        $assignedToId = isset($queryParameters['assignedToId']) ? $queryParameters['assignedToId'] : null;
        $status = isset($queryParameters['status']) ? $queryParameters['status'] : null;
        $accountHash = isset($queryParameters['accountHash']) ? $queryParameters['accountHash'] : null;
        $poaCaseNumbers = isset($queryParameters['poaCaseNumbers']) ? explode(',', $queryParameters['poaCaseNumbers']) : null;
        $orderBy = isset($queryParameters['orderBy']) ? $queryParameters['orderBy'] : null;
        $sort = isset($queryParameters['sort']) ? $queryParameters['sort'] : null;

        //  Search claims
        $claimSummaryPage = $this->claimService->search($page, $pageSize, $search, $assignedToId, $status, $accountHash, $poaCaseNumbers, $orderBy, $sort);

        return new JsonResponse($claimSummaryPage->getArrayCopy());
    }
}