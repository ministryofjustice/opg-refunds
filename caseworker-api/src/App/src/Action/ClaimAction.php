<?php

namespace App\Action;

use App\Exception\InvalidInputException;
use App\Service\Claim as ClaimService;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

use Opg\Refunds\Log\Initializer;

/**
 * Class ClaimAction
 * @package App\Action
 */
class ClaimAction extends AbstractRestfulAction implements Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;

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
     * @return JsonResponse
     */
    public function indexAction(ServerRequestInterface $request)
    {
        $claimId = $request->getAttribute('id');

        $identity = $request->getAttribute('identity');

        //  Return a specific claim
        $claim = $this->claimService->get($claimId, $identity->getId());

        $this->getLogger()->info('Claim viewed: '.$claim->getId(), [
            'claim'=>$claim->getId(),
            'user'=>$identity->getId(),
        ]);

        return new JsonResponse($claim->getArrayCopy());
    }

    /**
     * MODIFY/PATCH modify action
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws InvalidInputException
     */
    public function modifyAction(ServerRequestInterface $request)
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

        if (isset($requestBody['status'])) {
            $status = $requestBody['status'];

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
                if (!isset($requestBody['duplicateOfClaimId']) || IdentFormatter::parseId($requestBody['duplicateOfClaimId']) === false) {
                    throw new InvalidInputException('duplicateOfClaimId is required and must be a valid claim id');
                }

                $this->claimService->setStatusDuplicate($claimId, $identity->getId(), IdentFormatter::parseId($requestBody['duplicateOfClaimId']));
            } elseif ($status === ClaimModel::STATUS_WITHDRAWN) {
                $this->claimService->setStatusWithdrawn($claimId, $identity->getId());
            }
        }

        if (isset($requestBody['outcomeLetterSent'])) {
            $this->claimService->setOutcomeLetterSent($claimId, $identity->getId(), $requestBody['outcomeLetterSent']);
        }

        if (isset($requestBody['outcomePhoneCalled'])) {
            $this->claimService->setOutcomePhoneCalled($claimId, $identity->getId(), $requestBody['outcomePhoneCalled']);
        }

        $claim = $this->claimService->get($claimId, $identity->getId());

        return new JsonResponse($claim->getArrayCopy());
    }
}
