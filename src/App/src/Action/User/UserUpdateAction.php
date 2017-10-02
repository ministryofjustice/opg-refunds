<?php

namespace App\Action\User;

use App\Action\AbstractModelAction;
use App\Form\User;
use App\Service\User\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;

/**
 * Class UserUpdateAction
 * @package App\Action
 */
class UserUpdateAction extends AbstractModelAction
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserUpdateAction constructor.
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
        $user = null;

        if (!is_null($this->modelId)) {
            $user = $this->userService->getUser($this->modelId);
        }

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

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|\Zend\Diactoros\Response\RedirectResponse
     * @throws Exception
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        if ($form->isValid()) {
            //  Set the new user as active
            $user = new UserModel($form->getData());

            try {
                $user = $this->userService->createUser($user);

                return $this->redirectToRoute('user', ['id' => $user->getId()]);
            } catch (Exception $ex) {
                //  If the exception indicates a conflict translate the message for display
                if ($ex->getCode() == 409) {
                    $form->setMessages([
                        'email' => [
                            'Email address already exists'
                        ]
                    ]);
                } else {
                    throw $ex;
                }
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::user-edit-page', [
            'form' => $form,
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
        $user = $this->userService->getUser($this->modelId);

        if (is_null($user)) {
            throw new Exception('Page not found', 404);
        }

        $form = $this->getForm($request);

        if ($form->isValid()) {
            try {
                $user = $this->userService->updateUser($user->getId(), $form->getData());

                return $this->redirectToRoute('user', ['id' => $user->getId()]);
            } catch (Exception $ex) {
                //  If the exception indicates a conflict translate the message for display
                if ($ex->getCode() == 409) {
                    $form->setMessages([
                        'email' => [
                            'Email address already exists'
                        ]
                    ]);
                } else {
                    throw $ex;
                }
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::user-edit-page', [
            'user' => $user,
            'form' => $form,
        ]));
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

        $form = new User([
            'csrf' => $session['meta']['csrf']
        ]);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());
        }

        return $form;
    }
}
