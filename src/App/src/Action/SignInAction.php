<?php

namespace App\Action;

use App\Form\SignIn;
use App\Service\Authentication\Result;
use App\Service\User\User as UserService;
use Alphagov\Notifications\Client as NotifyClient;
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
    private $authService;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var NotifyClient
     */
    private $notifyClient;

    /**
     * SignInAction constructor
     *
     * @param AuthenticationService $authService
     * @param UserService $userService
     * @param NotifyClient $notifyClient
     */
    public function __construct(
        AuthenticationService $authService,
        UserService $userService,
        NotifyClient $notifyClient
    ) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->notifyClient = $notifyClient;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return Response\HtmlResponse|Response\RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ($this->authService->hasIdentity()) {
            return $this->redirectToRoute('home');
        }

        //  There is no active session so continue
        $session = $request->getAttribute('session');

        $form = new SignIn([
            'csrf' => $session['meta']['csrf']
        ]);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                //  Set the session as the authentication storage and the credentials
                $email = $form->get('email')->getValue();
                $this->authService->getAdapter()
                    ->setEmail($email)
                    ->setPassword($form->get('password')->getValue());

                $result = $this->authService->authenticate();

                if ($result->isValid()) {
                    return $this->redirectToRoute('home');
                }

                if ($result->getCode() === Result::FAILURE_ACCOUNT_LOCKED) {
                    //  Reset the password and set the token in the email to the user
                    $user = $this->userService->resetPassword($email);

                    //  Generate the change password URL
                    $host = sprintf('%s://%s', $request->getUri()->getScheme(), $request->getUri()->getAuthority());

                    $changePasswordUrl = $host . $this->getUrlHelper()->generate('password.change', [
                            'token' => $user->getToken(),
                        ]);

                    //  Send the set password email to the new user
                    $this->notifyClient->sendEmail($email, '3346472f-a804-496c-b443-af317f4b16a5', [
                        'change-password-url' => $changePasswordUrl,
                    ]);

                    $form->setAuthError('account-locked');
                } else {
                    $form->setAuthError('auth-error');
                }
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::sign-in-page', [
            'form'      => $form,
            'messages' => $this->getFlashMessages($request)
        ]));
    }
}
