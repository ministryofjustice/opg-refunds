<?php

namespace App\Action\Claim;

use Api\Exception\ApiException;
use App\Form\AbstractForm;
use App\Form\ClaimDuplicate;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;
use RuntimeException;

/**
 * Class ClaimDuplicateAction
 * @package App\Action\Claim
 */
class ClaimDuplicateAction extends AbstractClaimAction
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
        } elseif ($claim->isClaimResolved()) {
            return $this->redirectToRoute('claim', ['id' => $claim->getId()]);
        } elseif (!$claim->canResolveAsDuplicate()) {
            throw new Exception('Claim cannot be resolved as a duplicate', 400);
        }

        /** @var ClaimDuplicate $form */
        $form = $this->getForm($request, $claim);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-duplicate-page', [
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

        /** @var ClaimDuplicate $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $formData = $form->getData();

            $duplicateOf = $formData['duplicate-of'];
            $duplicateOfClaimId = IdentFormatter::parseId($duplicateOf);

            if ($duplicateOfClaimId === $claim->getId()) {
                $form->setMessages(['duplicate-of' => ['You cannot resolve this claim as a duplicate of itself']]);
            } else {
                try {
                    $claim = $this->claimService->setStatusDuplicate($claim->getId(), $duplicateOfClaimId);

                    if ($claim === null) {
                        throw new RuntimeException('Failed to resolve claim with id: ' . $this->modelId . ' as a duplicate of ' . $duplicateOf);
                    }

                    $duplicateOfClaimCode = IdentFormatter::format($duplicateOfClaimId);
                    $this->setFlashInfoMessage($request, "Claim with reference {$claim->getReferenceNumber()} resolved as a duplicate of {$duplicateOfClaimCode} successfully");

                    return $this->redirectToRoute('home');
                } catch (ApiException $ex) {
                    if ($ex->getCode() === 400) {
                        $form->setMessages(['duplicate-of' => ['Claim code does not match an existing claim']]);
                    } else {
                        throw $ex;
                    }
                }
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-duplicate-page', [
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

        $form = new ClaimDuplicate([
            'claim'  => $claim,
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}
