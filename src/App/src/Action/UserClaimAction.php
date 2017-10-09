<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class UserClaimAction
 * @package App\Action
 */
class UserClaimAction extends AbstractRestfulAction
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
     * UPDATE/PUT edit action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $userId = $request->getAttribute('id');
        $claimId = $request->getAttribute('claimId');

        if ($claimId === null) {
            $result = $this->claimService->assignNextClaim($userId);
        } else {
            $result = $this->claimService->assignClaim($claimId, $userId);
        }

        return new JsonResponse($result);
    }

    /**
     * DELETE/DELETE delete action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function deleteAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $userId = $request->getAttribute('id');
        $claimId = $request->getAttribute('claimId');

        $this->claimService->unassignClaim($claimId, $userId);

        $claim = $this->claimService->get($claimId, $userId);

        return new JsonResponse($claim->getArrayCopy());
    }
}