<?php

namespace App\Action\Claim;

use Alphagov\Notifications\Client as NotifyClient;
use App\Form\AbstractForm;
use App\Form\ConfirmNotified;
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
 * Class ConfirmNotifiedAction
 * @package App\Action\Claim
 */
class ConfirmNotifiedAction extends AbstractClaimAction
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
        }

        /** @var ConfirmNotified $form */
        $form = $this->getForm($request, $claim);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-confirm-notified-page', [
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

        /** @var ConfirmNotified $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            if ($claim->shouldSendLetter()) {
                $claim = $this->claimService->setOutcomeLetterSent($claim->getId());
                $this->setFlashInfoMessage($request, "Successfully confirmed letter was sent to claimant");
            } elseif ($claim->shouldSendLetter()) {
                $claim = $this->claimService->setOutcomePhoneCalled($claim->getId());
                $this->setFlashInfoMessage($request, "Successfully confirmed claimant was phoned");
            } else {
                throw new Exception('No manual notifications required!', 400);
            }

            if ($claim === null) {
                throw new RuntimeException('Failed to accept claim with id: ' . $this->modelId);
            }

            return $this->redirectToRoute('claim', ['id' => $claim->getId()]);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-confirm-notified-page', [
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

        $form = new ConfirmNotified([
            'claim'  => $claim,
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}
