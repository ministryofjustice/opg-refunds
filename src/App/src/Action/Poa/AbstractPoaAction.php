<?php

namespace App\Action\Poa;

use App\Action\AbstractClaimAction;
use App\Form\Poa;
use Exception;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

abstract class AbstractPoaAction extends AbstractClaimAction
{
    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        return new HtmlResponse($this->getTemplateRenderer()->render($this->templateName, [
            'form'  => $form,
            'claim' => $this->getClaim($request)
        ]));
    }

    /**
     * GET/POST add action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|\Zend\Diactoros\Response\RedirectResponse
     * @throws Exception
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        $form = $this->getForm($request);

        if ($request->getMethod() == 'POST') {
            /** @var Poa $form */
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $poa = new PoaModel($form->getModelData());
                //$message = $form->get('message')->getValue();

                /*$log = $this->claimService->addLog($this->modelId, 'Caseworker note', $message);

                if ($log === null) {
                    throw new RuntimeException('Failed to add new log to claim with id: ' . $this->modelId);
                }*/

                return $this->redirectToRoute('claim', ['id' => $request->getAttribute('claimId')]);
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render($this->templateName, [
            'claim' => $claim,
            'form'  => $form
        ]));
    }
}