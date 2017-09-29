<?php

namespace App\Action\Claim;

use App\Action\AbstractClaimAction;
use App\Form\AbstractForm;
use App\Form\ClaimReject;
use App\Service\Claim\Claim as ClaimService;
use App\Service\Poa\PoaFormatter as PoaFormatterService;
use Exception;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Zend\Diactoros\Response\HtmlResponse;

class ClaimRejectAction extends AbstractClaimAction
{
    /**
     * @var PoaFormatterService
     */
    private $poaFormatterService;

    public function __construct(ClaimService $claimService, PoaFormatterService $poaFormatterService)
    {
        parent::__construct($claimService);
        $this->poaFormatterService = $poaFormatterService;
    }

    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        /** @var ClaimReject $form */
        $form = $this->getForm($request, $claim);

        if ($claim === null) {
            throw new Exception('Claim not found', 404);
        }

        if (!$this->poaFormatterService->isClaimComplete($claim)) {
            throw new Exception('Claim is not complete', 400);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-reject-page', [
            'form'  => $form,
            'claim' => $claim
        ]));
    }

    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        /** @var ClaimReject $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $formData = $form->getData();

            $rejectionReason = $formData['rejection-reason'];
            $rejectionReasonDescription = $formData['rejection-reason-description'];

            $claim = $this->claimService->setRejectionReason($claim->getId(), $rejectionReason, $rejectionReasonDescription);

            if ($claim === null) {
                throw new RuntimeException('Failed to set rejection reason on claim with id: ' . $this->modelId);
            }

            return $this->redirectToRoute('home');
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-reject-page', [
            'form'  => $form,
            'claim' => $claim
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @return AbstractForm
     */
    public function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm
    {
        $session = $request->getAttribute('session');
        $form = new ClaimReject([
            'claim'  => $claim,
            'csrf'   => $session['meta']['csrf'],
        ]);
        return $form;
    }
}