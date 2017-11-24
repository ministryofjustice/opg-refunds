<?php

namespace App\Action\Claim;

use Alphagov\Notifications\Client as NotifyClient;
use App\Form\AbstractForm;
use App\Form\ClaimReject;
use App\Service\Claim\Claim as ClaimService;
use App\View\Details\DetailsFormatterPlatesExtension;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;
use RuntimeException;

/**
 * Class ClaimRejectAction
 * @package App\Action\Claim
 */
class ClaimRejectAction extends AbstractClaimAction
{
    /**
     * @var NotifyClient
     */
    private $notifyClient;

    /**
     * ClaimRejectAction constructor
     * @param ClaimService $claimService
     * @param NotifyClient $notifyClient
     */
    public function __construct(ClaimService $claimService, NotifyClient $notifyClient)
    {
        parent::__construct($claimService);
        $this->notifyClient = $notifyClient;
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
        } elseif (!$claim->isClaimComplete()) {
            throw new Exception('Claim is not complete', 400);
        }

        /** @var ClaimReject $form */
        $form = $this->getForm($request, $claim);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-reject-page', [
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

        /** @var ClaimReject $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $formData = $form->getData();

            $rejectionReason = $formData['rejection-reason'];
            $rejectionReasonDescription = $formData['rejection-reason-description'];

            $claim = $this->claimService
                ->setRejectionReason($claim->getId(), $rejectionReason, $rejectionReasonDescription);

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
    protected function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm
    {
        $session = $request->getAttribute('session');

        $form = new ClaimReject([
            'claim'  => $claim,
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}
