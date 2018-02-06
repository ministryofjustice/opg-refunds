<?php

namespace App\Action\User;

use Alphagov\Notifications\Client as NotifyClient;
use App\Action\AbstractModelAction;
use App\Service\User\User as UserService;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractUserAction
 * @package App\Action
 */
abstract class AbstractUserAction extends AbstractModelAction
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
     * AbstractUserAction constructor
     *
     * @param UserService $userService
     * @param NotifyClient|null $notifyClient
     */
    public function __construct(UserService $userService, NotifyClient $notifyClient = null)
    {
        $this->userService = $userService;
        $this->notifyClient = $notifyClient;
    }

    /**
     * @param ServerRequestInterface $request
     * @param User $user
     */
    protected function sendAccountSetUpEmail(ServerRequestInterface $request, User $user)
    {
        /** @var User $sessionUser */
        $sessionUser = $request->getAttribute('identity');

        //  Generate the change password URL
        $host = sprintf('%s://%s', $request->getUri()->getScheme(), $request->getUri()->getAuthority());

        $changePasswordUrl = $host . $this->getUrlHelper()->generate('password.change', [
            'token' => $user->getToken(),
        ]);

        //  Send the set password email to the new user
        $this->notifyClient->sendEmail($user->getEmail(), 'e5bc1a56-a630-4d12-b71d-e7e2c223f96b', [
            'creator-name'        => $sessionUser->getName(),
            'change-password-url' => $changePasswordUrl,
        ]);
    }
}
