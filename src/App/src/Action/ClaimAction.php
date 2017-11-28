<?php

namespace App\Action;

use App\Exception\InvalidInputException;
use App\Service\Claim as ClaimService;
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

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
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

        $identity = $request->getAttribute('identity');

        //  Return a specific claim
        $claim = $this->claimService->get($claimId, $identity->getId());

        return new JsonResponse($claim->getArrayCopy());
    }

    /**
     * MODIFY/PATCH modify action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     * @throws InvalidInputException
     */
    public function modifyAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claimId = $request->getAttribute('id');

        $identity = $request->getAttribute('identity');

        $requestBody = $request->getParsedBody();

        if (isset($requestBody['noSiriusPoas'])) {
            $this->claimService->setNoSiriusPoas($claimId, $identity->getId(), $requestBody['noSiriusPoas']);
        }

        if (isset($requestBody['noMerisPoas'])) {
            $this->claimService->setNoMerisPoas($claimId, $identity->getId(), $requestBody['noMerisPoas']);
        }

        $status = $requestBody['status'];

        if (isset($status)) {
            if ($status === ClaimModel::STATUS_ACCEPTED) {
                $this->claimService->setStatusAccepted($claimId, $identity->getId());
            } elseif ($status === ClaimModel::STATUS_REJECTED) {
                if (!isset($requestBody['rejectionReason']) || !isset($requestBody['rejectionReasonDescription'])) {
                    throw new InvalidInputException('Rejection reason and description are required');
                }

                $this->claimService->setStatusRejected(
                    $claimId,
                    $identity->getId(),
                    $requestBody['rejectionReason'],
                    $requestBody['rejectionReasonDescription']
                );
            } elseif ($status === ClaimModel::STATUS_IN_PROGRESS) {
                if (!isset($requestBody['reason'])) {
                    throw new InvalidInputException('Reason is required');
                }

                $this->claimService->setStatusInProgress($claimId, $identity->getId(), $requestBody['reason']);
            } elseif ($status === ClaimModel::STATUS_DUPLICATE) {
                if (!isset($requestBody['duplicateOfClaimId']) || !is_int($requestBody['duplicateOfClaimId'])) {
                    throw new InvalidInputException('duplicateOfClaimId is required and must be a valid claim id');
                }

                $this->claimService->setStatusDuplicate($claimId, $identity->getId(), $requestBody['duplicateOfClaimId']);
            }
        }

        $claim = $this->claimService->get($claimId, $identity->getId());

        return new JsonResponse($claim->getArrayCopy());
    }
}