<?php

namespace App\Action\Poa;

use App\Form\AbstractForm;
use App\Form\PoaDelete;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;
use RuntimeException;

/**
 * Class PoaDeleteAction
 * @package App\Action\Poa
 */
class PoaDeleteAction extends AbstractPoaAction
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

        $poa = $this->getPoa($claim);

        if ($poa === null) {
            throw new Exception('POA not found', 404);
        }

        $system = $request->getAttribute('system');

        $form = $this->getForm($request, $claim);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::poa-delete-page', [
            'claim'  => $claim,
            'poa'    => $poa,
            'system' => $system,
            'form'   => $form,
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Zend\Diactoros\Response\RedirectResponse
     * @throws Exception
     */
    public function deleteAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $claim = $this->claimService->deletePoa($claim->getId(), $this->modelId);

            return $this->redirectToRoute('claim', ['id' => $claim->getId()]);
        }

        // The only reason the form can be invalid is a CSRF check fail so no need to recover gracefully
        throw new Exception('CSRF failure', 500);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @return AbstractForm
     */
    protected function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm
    {
        $session = $request->getAttribute('session');

        $form = new PoaDelete([
            'csrf' => $session['meta']['csrf'],
        ]);

        return $form;
    }
}