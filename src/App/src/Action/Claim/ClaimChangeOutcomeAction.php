<?php

namespace App\Action\Claim;

use Api\Exception\ApiException;
use App\Form\AbstractForm;
use App\Form\ClaimChangeOutcome;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;

class ClaimChangeOutcomeAction extends AbstractClaimAction
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
        } elseif ($claim->isClaimResolved() === false) {
            return $this->redirectToRoute('claim', ['id' => $claim->getId()]);
        }

        /** @var ClaimChangeOutcome $form */
        $form = $this->getForm($request, $claim);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-change-outcome-page', [
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

        /** @var ClaimChangeOutcome $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        $poaCaseNumbers = [];

        if ($form->isValid()) {
            $formData = $form->getData();

            $reason = $formData['reason'];

            try {
                $claim = $this->claimService->changeClaimOutcome($claim->getId(), $reason);

                if ($claim === null) {
                    throw new RuntimeException('Failed to change outcome claim with id: ' . $this->modelId);
                }

                $this->setFlashInfoMessage($request, 'Claim outcome changed. Status changed to in progress');

                return $this->redirectToRoute('claim', ['id' => $claim->getId()]);
            } catch (ApiException $ex) {
                if ($ex->getCode() === 409) {
                    $form->setMessages(['general' => ['Could not change claim outcome. At least one other claim containing one of the same POA case numbers is being worked on. Search for claims that use these POA case numbers to resolve']]);
                    $poaCaseNumbers = [];
                    foreach ($claim->getPoas() as $poa) {
                        $poaCaseNumbers[] = $poa->getCaseNumber();
                    }
                } else {
                    throw $ex;
                }
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-change-outcome-page', [
            'form'  => $form,
            'claim' => $claim,
            'poaCaseNumbers' => join(',', $poaCaseNumbers)
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

        $form = new ClaimChangeOutcome([
            'claim'  => $claim,
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}