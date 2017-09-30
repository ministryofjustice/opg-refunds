<?php

namespace App\Action\Claim;

use App\Form\AbstractForm;
use App\Form\ClaimApprove;
use App\Service\Claim\Claim as ClaimService;
use App\Service\Poa\PoaFormatter as PoaFormatterService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;

/**
 * Class ClaimApproveAction
 * @package App\Action\Claim
 */
class ClaimApproveAction extends AbstractClaimAction
{
    /**
     * @var PoaFormatterService
     */
    private $poaFormatterService;

    /**
     * ClaimApproveAction constructor
     * @param ClaimService $claimService
     * @param PoaFormatterService $poaFormatterService
     */
    public function __construct(ClaimService $claimService, PoaFormatterService $poaFormatterService)
    {
        parent::__construct($claimService);
        $this->poaFormatterService = $poaFormatterService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     * @throws Exception
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        if ($claim === null) {
            throw new Exception('Claim not found', 404);
        } elseif (!$this->poaFormatterService->isClaimComplete($claim) || !$this->poaFormatterService->isClaimVerified($claim)) {
            throw new Exception('Claim is not complete or verified', 400);
        }

        /** @var ClaimApprove $form */
        $form = $this->getForm($request, $claim);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-approve-page', [
            'form'  => $form,
            'claim' => $claim
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|\Zend\Diactoros\Response\RedirectResponse
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        /** @var ClaimApprove $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $claim = $this->claimService->setStatusAccepted($claim->getId());

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

        $form = new ClaimApprove([
            'claim'  => $claim,
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}