<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;

class SignOutAction extends AbstractAction
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  If the user is authenticated then logout and destroy the session
        if ($this->authenticated($request)) {
            $session = $request->getAttribute('session');
            $session->destroy();
        }

        return $this->redirectToRoute('sign.in');
    }
}
