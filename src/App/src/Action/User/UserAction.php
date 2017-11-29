<?php

namespace App\Action\User;

use App\Action\AbstractModelAction;
use App\Service\User\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class UserAction
 * @package App\Action
 */
class UserAction extends AbstractModelAction
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserAction constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!is_null($this->modelId)) {
            //  Get the specific user
            $user = $this->userService->getUser($this->modelId);

            return new HtmlResponse($this->getTemplateRenderer()->render('app::user-page', [
                'user' => $user,
            ]));
        }

        //  Get all users
        $userSummaryPage = $this->userService->searchUsers();

        return new HtmlResponse($this->getTemplateRenderer()->render('app::users-page', [
            'userSummaryPage' => $userSummaryPage,
        ]));
    }
}
