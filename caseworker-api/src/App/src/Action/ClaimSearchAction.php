<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
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
     *
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request)
    {
        $queryParameters = $request->getQueryParams();

        //  Search claims
        $claimSummaryPage = $this->claimService->search($queryParameters);

        return new JsonResponse($claimSummaryPage->getArrayCopy());
    }
}
