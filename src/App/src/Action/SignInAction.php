<?php

namespace App\Action;

use App\Form\SignIn;
use App\Service\Session\Session;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Diactoros\Response;

/**
 * Class SignInAction
 * @package App\Action
 */
class SignInAction extends AbstractAction
{
    /**
     * @var AuthenticationService
     */
    private $auth;

    /**
     * SignInAction constructor
     *
     * @param AuthenticationService $auth
     */
    public function __construct(AuthenticationService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return Response\HtmlResponse|Response\RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  Check the session to see if there is already an authenticated session
        $session = $request->getAttribute('session');

        if ($session instanceof Session && $session->loggedIn()) {
            return $this->redirectToRoute('home');
        }

        //  There is no active session so continue
        $form = new SignIn([
            'csrf' => $session['meta']['csrf']
        ]);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                //  Set the session as the authentication storage and the credentials
//                $this->auth->setStorage($session)
//                    ->getAdapter()
//                    ->setEmail($form->get('email')->getValue())
//                    ->setPassword($form->get('password')->getValue());
//
//                $result = $this->auth->authenticate();

//                if ($result->isValid()) {
                    $session->setIdentity(new \stdClass());//$result->getIdentity());

                    return $this->redirectToRoute('home');
//                } else {
//                    //  TODO - Extract the error message from the result
//                    var_dump($result->getMessages());die();
//                }
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::sign-in-page', [
            'form' => $form,
        ]));
    }
}
