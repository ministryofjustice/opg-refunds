<?php

namespace App\Action\User;

use App\Form\UserDelete;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Exception;

/**
 * Class UserDeleteAction
 * @package App\Action
 */
class UserDeleteAction extends AbstractUserAction
{
    /**
     * @param ServerRequestInterface $request
     * @return HtmlResponse|\Laminas\Diactoros\Response\RedirectResponse
     * @throws Exception
     */
    public function indexAction(ServerRequestInterface $request)
    {
        $user = $this->userService->getUser($this->modelId);

        if (is_null($user)) {
            throw new Exception('User not found', 404);
        }

        /** @var User $identity */
        $identity = $request->getAttribute('identity');

        //  The user should not be able to access the delete screen for themself
        if ($identity->getId() == $this->modelId) {
            return $this->redirectToRoute('user', ['id' => $this->modelId]);
        }

        $form = $this->getForm($request);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::user-delete-page', [
            'user' => $user,
            'form' => $form,
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Laminas\Diactoros\Response\RedirectResponse
     * @throws Exception
     */
    public function deleteAction(ServerRequestInterface $request)
    {
        $form = $this->getForm($request);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            //  Don't allow the user to delete themselves

            /** @var User $identity */
            $identity = $request->getAttribute('identity');

            //  Only execute the delete if it is NOT the logged in user
            if ($identity->getId() != $this->modelId) {
                $user = $this->userService->deleteUser($this->modelId);
            }

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
