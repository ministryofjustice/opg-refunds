<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Session\SessionManager;

/**
 * Class SignOutAction
 * @package App\Action
 */
class SignOutAction extends AbstractAction
{
    /**
     * @var AuthenticationService
     */
    private $authService;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * SignInAction constructor
     *
     * @param AuthenticationService $authService
     * @param SessionManager $sessionManager
     */
    public function __construct(AuthenticationService $authService, SessionManager $sessionManager)
    {
        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Zend\Diactoros\Response\RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ($this->authService->hasIdentity()) {
            //  Clear the identity
            $this->authService->clearIdentity();
        }

        //  Destroy the session
        $this->sessionManager->destroy([
            'clear_storage' => true
        ]);

        return $this->redirectToRoute('sign.in');
    }
}
