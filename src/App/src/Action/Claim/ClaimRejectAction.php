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

            $sendRejectionMessage = true;
            $smsTemplate = false;

            $emailPersonalisation = [
                'no-poas-found'         => 'no',
                'no-fees-paid'          => 'no',
                'poas-already-refunded' => 'no',
                'details-not-verified'  => 'no',
            ];

            switch ($claim->getRejectionReason()) {
                case ClaimModel::REJECTION_REASON_NOT_IN_DATE_RANGE:
                case ClaimModel::REJECTION_REASON_NO_DONOR_LPA_FOUND:
                    $emailPersonalisation['no-poas-found'] = 'yes';
                    $smsTemplate = 'f90cdca8-cd8b-4e22-ac66-d328b219f53e';
                    break;
                case ClaimModel::REJECTION_REASON_PREVIOUSLY_REFUNDED:
                    $emailPersonalisation['poas-already-refunded'] = 'yes';
                    $smsTemplate = '5ccfdd66-0040-423a-8426-1458f912d41a';
                    break;
                case ClaimModel::REJECTION_REASON_NO_FEES_PAID:
                    $emailPersonalisation['no-fees-paid'] = 'yes';
                    $smsTemplate = '80b81c91-667e-47d8-bd8e-b87fdfa1b3de';
                    break;
                case ClaimModel::REJECTION_REASON_CLAIM_NOT_VERIFIED:
                    $emailPersonalisation['details-not-verified'] = 'yes';
                    $smsTemplate = '2bb54224-0cab-44b9-9623-fd12f6ee6e77';
                    break;
                case ClaimModel::REJECTION_REASON_OTHER:
                default:
                    $sendRejectionMessage = false;
            }

            if ($sendRejectionMessage) {
                $contact = $claim->getApplication()->getContact();
                $contactName = $claim->getApplication()->getApplicant() === 'attorney' ?
                    DetailsFormatterPlatesExtension::getFormattedName(
                        $claim->getApplication()->getAttorney()->getCurrent()->getName()
                    ) : $claim->getDonorName();

                if ($contact->hasEmail()) {
                    $this->notifyClient->sendEmail($contact->getEmail(), '018ab571-a2a5-41e6-a1d4-ae369e2d3cd1', array_merge($emailPersonalisation, [
                        'person-completing' => $contactName,
                        'donor-name' => $claim->getDonorName(),
                        'claim-code' => $claim->getReferenceNumber()
                    ]));
                }

                if ($contact->hasPhone() && substr($contact->getPhone(), 0, 2) === '07' && $smsTemplate) {
                    $this->notifyClient->sendSms($contact->getPhone(), $smsTemplate, [
                        'donor-name' => $claim->getDonorName(),
                        'claim-code' => $claim->getReferenceNumber()
                    ]);
                }
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
