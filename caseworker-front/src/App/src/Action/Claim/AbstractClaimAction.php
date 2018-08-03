<?php

namespace App\Action\Claim;

use App\Action\AbstractModelAction;
use App\Form\AbstractForm;
use App\Service\Claim\Claim as ClaimService;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractClaimAction
 * @package App\Action\Claim
 */
abstract class AbstractClaimAction extends AbstractModelAction
{
    /**
     * @var ClaimService
     */
    protected $claimService;

    /**
     * AbstractClaimAction constructor
     * @param ClaimService $claimService
     */
    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ClaimModel
     */
    protected function getClaim(ServerRequestInterface $request): ClaimModel
    {
        //  Retrieve claim to verify it exists and the user has access to it
        $claimId = $request->getAttribute('claimId') ?: $this->modelId;

        return $this->claimService->getClaim($claimId);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @return AbstractForm
     */
    abstract protected function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm;
}
