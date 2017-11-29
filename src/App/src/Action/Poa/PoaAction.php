<?php

namespace App\Action\Poa;

use Api\Exception\ApiException;
use App\Form\AbstractForm;
use App\Form\Poa;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;
use RuntimeException;

/**
 * Class PoaAction
 * @package App\Action\Poa
 */
class PoaAction extends AbstractPoaAction
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

        /** @var Poa $form */
        $form = $this->getForm($request, $claim, true);

        $system = $request->getAttribute('system');

        return new HtmlResponse($this->getTemplateRenderer()->render('app::poa-page', [
            'form'   => $form,
            'claim'  => $claim,
            'system' => $system,
            'poaId'  => $this->modelId,
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
        $system = $request->getAttribute('system');

        $form = $this->getForm($request, $claim);

        /** @var Poa $form */
        $form->setData($request->getParsedBody());

        $poaCaseNumbers = [];

        if ($form->isValid()) {
            $poa = new PoaModel($form->getModelData());

            try {
                $claim = $this->claimService->addPoa($claim, $poa);

                if ($claim === null) {
                    throw new RuntimeException('Failed to add new POA to claim with id: ' . $this->modelId);
                }

                //TODO: Find a better way
                if ($_POST['submit'] === 'Save and add another') {
                    return $this->redirectToRoute('claim.poa', [
                        'claimId' => $request->getAttribute('claimId'),
                        'system'  => $system,
                        'id'      => null
                    ]);
                }

                return $this->redirectToRoute('claim', ['id' => $request->getAttribute('claimId')]);
            } catch (ApiException $ex) {
                if ($ex->getCode() === 400) {
                    $form->setMessages(['case-number' => ['Case number is already registered with at least one claim. Search for claims that use this case number to resolve']]);
                    $poaCaseNumbers[] = $poa->getCaseNumber();
                } else {
                    throw $ex;
                }
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::poa-page', [
            'claim'  => $claim,
            'form'   => $form,
            'system' => $system,
            'poaCaseNumbers' => join(',', $poaCaseNumbers)
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|\Zend\Diactoros\Response\RedirectResponse
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);
        $system = $request->getAttribute('system');

        /** @var Poa $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $poa = new PoaModel($form->getModelData());

            try {
                $claim = $this->claimService->editPoa($claim, $poa, $this->modelId);

                if ($claim === null) {
                    throw new RuntimeException('Failed to edit POA with id: ' . $this->modelId);
                }

                //TODO: Find a better way
                if ($_POST['submit'] === 'Save and add another') {
                    return $this->redirectToRoute('claim.poa', [
                        'claimId' => $request->getAttribute('claimId'),
                        'system'  => $system,
                        'id'      => null
                    ]);
                }

                return $this->redirectToRoute('claim', ['id' => $request->getAttribute('claimId')]);
            } catch (ApiException $ex) {
                if ($ex->getCode() === 400) {
                    $form->setMessages(['case-number' => ['Case number is already registered with another claim']]);
                } else {
                    throw $ex;
                }
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::poa-page', [
            'form'   => $form,
            'claim'  => $claim,
            'system' => $request->getAttribute('system')
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @param bool $bind
     * @return AbstractForm
     * @throws Exception
     */
    protected function getForm(ServerRequestInterface $request, ClaimModel $claim, bool $bind = false): AbstractForm
    {
        $poa = null;

        if ($this->modelId !== null) {
            $poa = $this->getPoa($claim);

            if ($poa === null) {
                throw new Exception('POA not found', 404);
            }
        }

        $session = $request->getAttribute('session');

        $form = new Poa([
            'claim'  => $claim,
            'poa'    => $poa,
            'system' => $request->getAttribute('system'),
            'csrf'   => $session['meta']['csrf'],
        ]);

        if ($bind === true) {
            $form->bindModelData($poa);
        }

        return $form;
    }
}
