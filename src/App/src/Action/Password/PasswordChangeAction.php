<?php

namespace App\Action\Password;

use App\Action\AbstractAction;
use App\Form\SetPassword;
use App\Service\User\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;

/**
 * Class PasswordChangeAction
 * @package App\Action\Password
 */
class PasswordChangeAction extends AbstractAction
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * PasswordChangeAction constructor
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|\Zend\Diactoros\Response\RedirectResponse
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /** @var User $user */
        $user = $request->getAttribute('identity');
        $loggedIn = !is_null($user);

        $token = $request->getAttribute('token');

        //  If the user is logged in a token can not be provided
        if (!empty($token) && $loggedIn) {
            throw new Exception('Password tokens can not be provided for authenticated users', 403);
        } elseif (empty($token) && !$loggedIn) {
            //  If no token has been provided and the user isn't logged in then bounce them
            return $this->redirectToRoute('sign.in');
        }

        //  If a token was provided then get the user details now
        if (!empty($token)) {
            //  If a token was provided get the user details and check that they are pending
            $user = $this->userService->getUserByToken($token);

            if ($user->getStatus() != User::STATUS_PENDING) {
                throw new Exception('Token has been used', 403);
            }
        }

        //  Process the request - set up the set password form
        $session = $request->getAttribute('session');

        $form = new SetPassword([
            'csrf' => $session['meta']['csrf']
        ]);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                //  Set the password for the user
                $password = $request->getParsedBody()['password'];

                if ($loggedIn) {
                    $user = $this->userService->updatePassword($user->getId(), $password);
                } else {
                    $user = $this->userService->updatePasswordByToken($user->getToken(), $password);
                }

                return new HtmlResponse($this->getTemplateRenderer()->render('app::password-changed-page', [
                    'loggedIn' => $loggedIn,
                ]));
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::password-change-page', [
            'form' => $form,
        ]));
    }
}
