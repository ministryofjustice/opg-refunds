<?php

namespace App\Action\Poa;

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
        $form = $this->getForm($request, $claim);

        if ($this->modelId !== null) {
            //Edit page
            $poa = $this->getPoa($claim);

            if ($poa === null) {
                throw new Exception('POA not found', 404);
            }

            $form->bindModelData($poa);
        }

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

        if ($form->isValid()) {
            $poa = new PoaModel($form->getModelData());

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
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::poa-page', [
            'claim'  => $claim,
            'form'   => $form,
            'system' => $system
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
     * @return AbstractForm
     */
    protected function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm
    {
        $session = $request->getAttribute('session');

        $form = new Poa([
            'claim'  => $claim,
            'system' => $request->getAttribute('system'),
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}