<?php

namespace App\Action\Claim;

use Alphagov\Notifications\Client as NotifyClient;
use App\Form\AbstractForm;
use App\Form\ClaimApprove;
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
 * Class ClaimApproveAction
 * @package App\Action\Claim
 */
class ClaimApproveAction extends AbstractClaimAction
{
    /**
     * @var NotifyClient
     */
    private $notifyClient;

    /**
     * ClaimApproveAction constructor
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
        } elseif (!$claim->isClaimComplete() || !$claim->isClaimVerified() || !$claim->isClaimRefundNonZero()) {
            throw new Exception('Claim is not complete or verified or has a total refund of £0.00', 400);
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

            if ($claim === null) {
                throw new RuntimeException('Failed to accept claim with id: ' . $this->modelId);
            }

            $contact = $claim->getApplication()->getContact();
            $contactName = $claim->getApplication()->getApplicant() === 'attorney' ?
                DetailsFormatterPlatesExtension::getFormattedName(
                    $claim->getApplication()->getAttorney()->getCurrent()->getName()
                ) : $claim->getDonorName();

            if ($contact->hasEmail()) {
                $this->notifyClient->sendEmail($contact->getEmail(), '810b6370-7162-4d9a-859c-34b61f3fecde', [
                    'person-completing' => $contactName,
                    'amount-including-interest' => PoaFormatterPlatesExtension::getRefundTotalAmountString($claim),
                    'interest-amount' => PoaFormatterPlatesExtension::getMoneyString($claim->getRefundInterestAmount()),
                    'donor-name' => $claim->getDonorName(),
                    'claim-code' => $claim->getReferenceNumber()
                ]);
            }

            if ($contact->hasPhone() && substr($contact->getPhone(), 0, 2) === '07') {
                $this->notifyClient->sendSms($contact->getPhone(), 'df4ffd99-fcb0-4f77-b001-0c89b666d02f', [
                    'amount-including-interest' => PoaFormatterPlatesExtension::getRefundTotalAmountString($claim),
                    'interest-amount' => PoaFormatterPlatesExtension::getMoneyString($claim->getRefundInterestAmount()),
                    'donor-name' => $claim->getDonorName(),
                    'claim-code' => $claim->getReferenceNumber()
                ]);
            }

            return $this->redirectToRoute('home');
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-approve-page', [
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

        $form = new ClaimApprove([
            'claim'  => $claim,
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}
