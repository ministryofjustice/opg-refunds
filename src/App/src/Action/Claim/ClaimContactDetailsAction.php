<?php

namespace App\Action\Claim;

use App\Form\AbstractForm;
use App\Form\ClaimContactDetails;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;

/**
 * Class ClaimContactDetailsAction
 * @package App\Action\Claim
 */
class ClaimContactDetailsAction extends AbstractClaimAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|\Zend\Diactoros\Response\RedirectResponse
     * @throws Exception
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        if ($claim === null) {
            throw new Exception('Claim not found', 404);
        }

        /** @var ClaimContactDetails $form */
        $form = $this->getForm($request, $claim);
        $form->setContactDetails($claim->getApplication()->getContact());

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-contact-details-page', [
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

        /** @var ClaimContactDetails $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $contactDetails = $form->getContactDetails();

            $claim = $this->claimService->editContactDetails($claim->getId(), $contactDetails);

            if ($claim === null) {
                throw new RuntimeException('Failed to update contact details for claim with id: ' . $this->modelId);
            }

            $this->setFlashInfoMessage($request, "Contact details updated successfully");

            return $this->redirectToRoute('claim', ['id' => $claim->getId()]);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-contact-details-page', [
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

        $form = new ClaimContactDetails([
            'claim'  => $claim,
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}
