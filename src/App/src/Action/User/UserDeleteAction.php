<?php

namespace App\Action\User;

use App\Action\AbstractModelAction;
use App\Form\UserDelete;
use App\Service\User\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;

/**
 * Class UserDeleteAction
 * @package App\Action
 */
class UserDeleteAction extends AbstractModelAction
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserDeleteAction constructor.
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
     * @throws Exception
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $user = $this->userService->getUser($this->modelId);

        if (is_null($user)) {
            throw new Exception('User not found', 404);
        }

        $form = $this->getForm($request);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::user-delete-page', [
            'user' => $user,
            'form' => $form,
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Zend\Diactoros\Response\RedirectResponse
     * @throws Exception
     */
    public function deleteAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $user = $this->userService->deleteUser($this->modelId);

            return $this->redirectToRoute('user');
        }

        // The only reason the form can be invalid is a CSRF check fail so no need to recover gracefully
        throw new Exception('CSRF failure', 500);
    }

    /**
     * @param ServerRequestInterface $request
     * @return UserDelete
     */
    private function getForm(ServerRequestInterface $request): UserDelete
    {
        $session = $request->getAttribute('session');

        $form = new UserDelete([
            'csrf' => $session['meta']['csrf'],
        ]);

        return $form;
    }
}
