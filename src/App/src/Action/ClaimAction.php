<?php

namespace App\Action;

use App\Form\AbstractForm;
use App\Form\Log;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ClaimAction
 * @package App\Action
 */
class ClaimAction extends AbstractClaimAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        $form = $this->getForm($request);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-page', [
            'claim' => $claim,
            'form'  => $form
        ]));
    }

    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //Even though we are adding a log message here,
        //we are technically editing the claim by adding a log message to it
        $claim = $this->getClaim($request);

        $form = $this->getForm($request);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $message = $form->get('message')->getValue();

                $log = $this->claimService->addLog($this->modelId, 'Caseworker note', $message);

                if ($log === null) {
                    throw new RuntimeException('Failed to add new log to claim with id: ' . $this->modelId);
                }

                return $this->redirectToRoute('claim', ['id' => $this->modelId]);
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-page', [
            'claim' => $claim,
            'form'  => $form
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return AbstractForm
     */
    public function getForm(ServerRequestInterface $request): AbstractForm
    {
        $session = $request->getAttribute('session');
        $form = new Log([
            'csrf' => $session['meta']['csrf']
        ]);
        return $form;
    }
}
