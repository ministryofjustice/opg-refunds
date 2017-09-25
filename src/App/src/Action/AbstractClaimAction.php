<?php

namespace App\Action;

use App\Form\AbstractForm;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use App\Service\Claim as ClaimService;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractClaimAction extends AbstractModelAction
{
    /**
     * @var ClaimService
     */
    protected $claimService;

    /**
     * ClaimAction constructor.
     * @param ClaimService $claimService
     */
    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Opg\Refunds\Caseworker\DataModel\Cases\Claim
     */
    public function getClaim(ServerRequestInterface $request): ClaimModel
    {
        //Retrieve claim to verify it exists and the user has access to it
        $claim = $this->claimService->getClaim($this->modelId, $request->getAttribute('identity')->getId());
        return $claim;
    }

    /**
     * @param ServerRequestInterface $request
     * @return AbstractForm
     */
    abstract public function getForm(ServerRequestInterface $request): AbstractForm;
}