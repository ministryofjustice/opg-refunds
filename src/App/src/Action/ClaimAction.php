<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class ClaimAction
 * @package App\Action
 */
class ClaimAction extends AbstractRestfulAction
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
        $claimId = $request->getAttribute('id');

        if ($claimId === null) {
            //  Get all of the claims
            $claims = $this->claimService->getAll();
            $claimsData = [];

            foreach ($claims as $claim) {
                $claimsData[] = $claim->toArray();
            }

            return new JsonResponse($claimsData);
        } else {
            //  Return a specific claim
            $claim = $this->claimService->get($claimId);

            return new JsonResponse($claim->toArray());
        }
    }
}