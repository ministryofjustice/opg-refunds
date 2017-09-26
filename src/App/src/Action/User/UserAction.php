<?php

namespace App\Action\User;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class UserAction
 * @package App\Action
 */
class UserAction extends AbstractUserAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $user = $this->getUser();

        if (is_null($user)) {
            //  Get all users
            $users = [];

            //  Even though the user details are in the session get them again with a GET call to the API
            $usersData = $this->getApiClient()->getUsers();

            foreach ($usersData as $userData) {
                $users[] = new UserModel($userData);
            }

            return new HtmlResponse($this->getTemplateRenderer()->render('app::users-page', [
                'users' => $users,
            ]));
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::user-page', [
            'user' => $user,
        ]));
    }
}
