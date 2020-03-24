<?php

namespace App\Action;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Session\SessionManager;

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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
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
