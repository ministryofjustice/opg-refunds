<?php

namespace App\Action;

use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use App\Form\Log;
use App\Service\ClaimService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ClaimAction
 * @package App\Action
 */
class ClaimAction extends AbstractModelAction
{
    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * ClaimAction constructor.
     * @param ClaimService $claimService
     */
    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

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
                //  Set the session as the authentication storage and the credentials
                /*$this->authService->getAdapter()
                    ->setEmail($form->get('email')->getValue())
                    ->setPassword($form->get('password')->getValue());

                $result = $this->authService->authenticate();

                if ($result->isValid()) {
                    return $this->redirectToRoute('home');
                } else {
                    //  There should be only one error
                    $authenticationError = $result->getMessages()[0];
                }*/
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-page', [
            'claim' => $claim,
            'form'  => $form
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Opg\Refunds\Caseworker\DataModel\Cases\Claim
     */
    public function getClaim(ServerRequestInterface $request): ClaimModel
    {
        //Retrieve claim to verify it exists and the user has access to it
        $claim = $this->claimService->getClaim($this->modelId, $request->getAttribute('identity')->getId());
        return $claim;
    }

    /**
     * @param ServerRequestInterface $request
     * @return Log
     */
    public function getForm(ServerRequestInterface $request): Log
    {
        $session = $request->getAttribute('session');
        $form = new Log([
            'csrf' => $session['meta']['csrf']
        ]);
        return $form;
    }
}