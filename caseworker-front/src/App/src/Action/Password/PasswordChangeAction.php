<?php

namespace App\Action\Password;

use Api\Exception\ApiException;
use App\Action\AbstractAction;
use App\Form\SetPassword;
use App\Service\User\User as UserService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
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
    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('identity');
        $loggedIn = !is_null($user);

        $token = $request->getAttribute('token');

        //  If the user is logged in a token can not be provided
        if (!empty($token)) {
            if ($loggedIn) {
                return new HtmlResponse($this->getTemplateRenderer()->render('app::account-setup-failure-page'));
            }

            //  The checks to determine if the user is in the correct state (i.e. token expires = -1) will take place in the API
            try {
                $user = $this->userService->getUserByToken($token);
            } catch (ApiException $aex) {
                $message = $aex->getMessage();

                if ($message == 'Account set up token has expired') {
                    return new HtmlResponse($this->getTemplateRenderer()->render('app::account-setup-token-expired-page'));
                } elseif ($message == 'Password reset token has expired') {
                    return new HtmlResponse($this->getTemplateRenderer()->render('app::password-reset-token-expired-page'));
                } else {
                    //  This is probably a user trying to use an old link - just bounce then to the login screen
                    return $this->redirectToRoute('sign.in');
                }
            }
        } else {
            if (!$loggedIn) {
                //  If no token has been provided and the user isn't logged in then bounce them
                return $this->redirectToRoute('sign.in');
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
