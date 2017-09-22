<?php

namespace App\Action\User;

use App\Action\AbstractModelAction;
use App\Form\User;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;
use Zend\Form\FormInterface;

/**
 * Class UserUpdateAction
 * @package App\Action
 */
class UserUpdateAction extends AbstractModelAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $user = $this->getUser();

        $form = $this->getForm($request);

        //  Bind any existing details to the form
        if (!is_null($user)) {
            $form->bind($user);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::user-edit-page', [
            'user' => $user,
            'form' => $form,
        ]));
    }

    //  TODO - addAction

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     * @throws Exception
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $user = $this->getUser();

        if (is_null($user)) {
            throw new Exception('Page not found', 404);
        }

        $form = $this->getForm($request);

        if ($request->getMethod() == 'POST') {
            //  TODO - Handle form validation and post here
        } else {
            $form->bind($user);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::user-edit-page', [
            'user' => $user,
            'form' => $form,
        ]));
    }

    //  TODO - deleteAction

    /**
     * Get the model concerned
     *
     * @return null|UserModel
     */
    private function getUser()
    {
        $user = null;

        if (is_numeric($this->modelId)) {
            $userData = $this->getApiClient()->getUser($this->modelId);

            $user = new UserModel($userData);
        }

        return $user;
    }

    /**
     * Get the form for the model concerned
     *
     * @param ServerRequestInterface $request
     * @return User
     */
    private function getForm(ServerRequestInterface $request)
    {
        $session = $request->getAttribute('session');

        return new User([
            'csrf' => $session['meta']['csrf']
        ]);
    }
}
