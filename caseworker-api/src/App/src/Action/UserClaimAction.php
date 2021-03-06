<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

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
     *
     * @return ResponseInterface
     */
    public function editAction(ServerRequestInterface $request)
    {
        $assignToUserId = $request->getAttribute('id');
        $claimId = $request->getAttribute('claimId');

        if ($claimId === null) {
            $result = $this->claimService->assignNextClaim($assignToUserId);
        } else {
            $identity = $request->getAttribute('identity');

            $requestBody = $request->getParsedBody();

            $result = $this->claimService->assignClaim($claimId, $identity->getId(), $assignToUserId, $requestBody['reason']);
        }

        return new JsonResponse($result);
    }

    /**
     * DELETE/DELETE delete action
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function deleteAction(ServerRequestInterface $request)
    {
        $userId = $request->getAttribute('id');
        $claimId = $request->getAttribute('claimId');

        $this->claimService->unassignClaim($claimId, $userId);

        $claim = $this->claimService->get($claimId, $userId);

        return new JsonResponse($claim->getArrayCopy());
    }
}
