<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use App\Service\User as UserService;
use Exception;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
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

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(ClaimService $claimService, UserService $userService)
    {
        $this->claimService = $claimService;
        $this->userService = $userService;
    }

    /**
     * READ/GET index action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claimId = $request->getAttribute('id');

        $token = $request->getHeaderLine('token');
        $user = $this->userService->getByToken($token);

        //  Return a specific claim
        $claim = $this->claimService->get($claimId, $user->getId());

        return new JsonResponse($claim->getArrayCopy());
    }

    /**
     * MODIFY/PATCH modify action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     * @throws Exception
     */
    public function modifyAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claimId = $request->getAttribute('id');

        $token = $request->getHeaderLine('token');
        $user = $this->userService->getByToken($token);

        $requestBody = $request->getParsedBody();

        if (isset($requestBody['noSiriusPoas'])) {
            $this->claimService->setNoSiriusPoas($claimId, $user->getId(), $requestBody['noSiriusPoas']);
        }

        if (isset($requestBody['noMerisPoas'])) {
            $this->claimService->setNoMerisPoas($claimId, $user->getId(), $requestBody['noMerisPoas']);
        }

        if (isset($requestBody['status'])) {
            if ($requestBody['status'] === ClaimModel::STATUS_ACCEPTED) {
                $this->claimService->setStatusAccepted($claimId, $user->getId());
            } elseif ($requestBody['status'] === ClaimModel::STATUS_REJECTED) {
                if (!isset($requestBody['rejectionReason']) || !isset($requestBody['rejectionReasonDescription'])) {
                    throw new Exception('Rejection reason and description are required', 400);
                }

                $this->claimService->setStatusRejected($claimId, $user->getId(), $requestBody['rejectionReason'], $requestBody['rejectionReasonDescription']);
            }
        }

        $claim = $this->claimService->get($claimId, $user->getId());

        return new JsonResponse($claim->getArrayCopy());
    }
}