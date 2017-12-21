<?php

namespace App\Action\Claim;

use Alphagov\Notifications\Client as NotifyClient;
use App\Form\AbstractForm;
use App\Form\ClaimWithdraw;
use App\Service\Claim\Claim as ClaimService;
use App\View\Details\DetailsFormatterPlatesExtension;
use App\View\Poa\PoaFormatterPlatesExtension;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;

/**
 * Class ClaimWithdrawAction
 * @package App\Action\Claim
 */
class ClaimWithdrawAction extends AbstractClaimAction
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

        if ($claim === null) {
            throw new Exception('Claim not found', 404);
        } elseif (!$claim->canWithdrawClaim()) {
            throw new Exception('Claim cannot be withdrawn', 400);
        }

        /** @var ClaimWithdraw $form */
        $form = $this->getForm($request, $claim);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-withdraw-page', [
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

        /** @var ClaimWithdraw $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $claim = $this->claimService->setStatusWithdrawn($claim->getId());

            if ($claim === null) {
                throw new RuntimeException('Failed to accept claim with id: ' . $this->modelId);
            }

            $this->setFlashInfoMessage($request, "Claim with reference {$claim->getReferenceNumber()} withdrawn successfully");

            return $this->redirectToRoute('home');
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-withdraw-page', [
            'form'  => $form,
            'claim' => $claim
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @return AbstractForm
     */
    protected function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm
    {
        $session = $request->getAttribute('session');

        $form = new ClaimWithdraw([
            'claim'  => $claim,
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}
