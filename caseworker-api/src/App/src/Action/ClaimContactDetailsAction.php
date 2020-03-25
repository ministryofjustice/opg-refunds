<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Applications\Contact as ContactDetailsModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

/**
 * Class ClaimContactDetailsAction
 * @package App\Action
 */
class ClaimContactDetailsAction extends AbstractRestfulAction
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
     * @return ResponseInterface
     */
    public function editAction(ServerRequestInterface $request)
    {
        $requestBody = $request->getParsedBody();
        $contactDetailsModel = new ContactDetailsModel($requestBody);

        $claimId = $request->getAttribute('claimId');

        $identity = $request->getAttribute('identity');

        $claimModel = $this->claimService->editContactDetails($claimId, $identity->getId(), $contactDetailsModel);

        return new JsonResponse($claimModel->getArrayCopy());
    }
}
