<?php

namespace App\Action;

use App\Service\Session\Session;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SignOutAction
 * @package App\Action
 */
class SignOutAction extends AbstractAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Zend\Diactoros\Response\RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');

        if ($session instanceof Session && $session->loggedIn()) {
            $session->destroy();
        }

        return $this->redirectToRoute('sign.in');
    }
}
