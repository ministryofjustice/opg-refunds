<?php

namespace App\Action\Poa;

use App\Action\Claim\AbstractClaimAction;
use App\Form\AbstractForm;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;
use RuntimeException;

/**
 * Class PoaDeleteAction
 * @package App\Action\Poa
 */
class PoaDeleteAction extends AbstractClaimAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     * @throws Exception
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);
        $system = $request->getAttribute('system');

        $poa = $this->getPoa($claim);
        if ($poa === null) {
            throw new Exception('POA not found', 404);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::poa-delete-page', [
            'claim'  => $claim,
            'poa'    => $poa,
            'system' => $system,
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Zend\Diactoros\Response\RedirectResponse
     */
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