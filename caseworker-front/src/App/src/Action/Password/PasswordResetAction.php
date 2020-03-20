<?php

namespace App\Action\Password;

use Api\Exception\ApiException;
use App\Action\AbstractAction;
use App\Form\ResetPassword;
use App\Service\User\User as UserService;
use Alphagov\Notifications\Client as NotifyClient;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;
use Laminas\Diactoros\Response;

/**
 * Class PasswordResetAction
 * @package App\Action\Password
 */
class PasswordResetAction extends AbstractAction
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var NotifyClient
     */
    private $notifyClient;

    /**
     * @param UserService $userService
     */
    public function __construct(UserService $userService, NotifyClient $notifyClient)
    {
        $this->userService = $userService;
        $this->notifyClient = $notifyClient;
    }

    /**
     * @param ServerRequestInterface $request
     * @return Response\HtmlResponse|Response\RedirectResponse
     */
    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        $identity = $request->getAttribute('identity');

        //  Users can not reset passwords while logged in
        if (!is_null($identity)) {
            return $this->redirectToRoute('home');
        }

        $session = $request->getAttribute('session');

        $form = new ResetPassword([
            'csrf' => $session['meta']['csrf']
        ]);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $email = $form->get('email')->getValue();

                try {
                    //  Reset the password and set the token in the email to the user
                    $user = $this->userService->resetPassword($email);

                    //  Generate the change password URL
                    $host = sprintf('%s://%s', $request->getUri()->getScheme(), $request->getUri()->getAuthority());

                    $changePasswordUrl = $host . $this->getUrlHelper()->generate('password.change', [
                        'token' => $user->getToken(),
                    ]);

                    //  Send the set password email to the new user
                    $this->notifyClient->sendEmail($email, '0f993cd8-41d4-4274-9a60-b35dcadad1a9', [
                        'change-password-url' => $changePasswordUrl,
                    ]);
                } catch (ApiException $ignore) {
                }

                /** @var Messages $flash */
                $flash = $request->getAttribute('flash');
                $flash->addMessage('info', 'Password reset email sent');

                return $this->redirectToRoute('sign.in');
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::password-reset-page', [
            'form'  => $form,
        ]));
    }
}
