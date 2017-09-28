<?php

namespace App\Action\Poa;

use App\Action\AbstractClaimAction;
use App\Form\AbstractForm;
use Exception;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Zend\Diactoros\Response\HtmlResponse;

class PoaDeleteAction extends AbstractClaimAction
{
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);
        $system = $request->getAttribute('system');

        $poa = $this->getPoa($claim);
        if ($poa === null) {
            throw new Exception('POA not found', 404);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::poa-delete-page', [
            'claim'     => $claim,
            'poa'       => $poa,
            'cancelUrl' => $this->getUrlHelper()->generate('claim.poa', [
                'claimId' => $request->getAttribute('claimId'),
                'system'  => $system,
                'id'      => $this->modelId
            ])
        ]));
    }

    public function deleteAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->claimService->deletePoa($request->getAttribute('claimId'), $this->modelId);

        if ($claim === null) {
            throw new RuntimeException('Failed to edit POA with id: ' . $this->modelId);
        }

        return $this->redirectToRoute('claim', ['id' => $request->getAttribute('claimId')]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @return AbstractForm
     */
    public function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm
    {
        return null;
    }
}