<?php

namespace App\Action;

use App\Form\User;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;
use Zend\Form\FormInterface;

/**
 * Class UserAction
 * @package App\Action
 */
class UserAction extends AbstractRestfulAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $userId = $request->getAttribute('id');

        if (is_numeric($userId)) {
            $userData = $this->getApiClient()->getUser($userId);

            $user = new UserModel($userData);

            return new HtmlResponse($this->getTemplateRenderer()->render('app::user-page', [
                'user' => $user,
            ]));
        }

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

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     * @throws Exception
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $userId = $request->getAttribute('id');
        $userData = [];

        if (is_numeric($userId)) {
            $userData = $this->getApiClient()->getUser($userId);
        }

        if (empty($userData)) {
            throw new Exception('Page not found', 404);
        }

        $user = new UserModel($userData);

        $session = $request->getAttribute('session');

        //  There is no active session so continue
        $form = new User([
            'csrf' => $session['meta']['csrf']
        ]);

        $authenticationError = null;

        if ($request->getMethod() == 'POST') {
            //  TODO - Handle form validation and post here
        } else {
            $form->bind($user);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::user-edit-page', [
            'user'  => $user,
            'form'  => $form,
            'error' => $authenticationError,
        ]));
    }
}
