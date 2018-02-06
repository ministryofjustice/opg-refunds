<?php

namespace App\Action\User;

use App\Form\UserDelete;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;

/**
 * Class UserDeleteAction
 * @package App\Action
 */
class UserDeleteAction extends AbstractUserAction
{
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

        /** @var User $identity */
        $identity = $request->getAttribute('identity');

        $deletingSelf = ($identity->getId() == $user->getId());

        $form = $this->getForm($request);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::user-delete-page', [
            'user'          => $user,
            'form'          => $form,
            'deletingSelf'  => $deletingSelf,
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

            /** @var User $identity */
            $identity = $request->getAttribute('identity');

            //  If the user just deleted their own account sign then out automatically - otherwise return to the users screen
            return $this->redirectToRoute($identity->getId() == $user->getId() ? 'sign.out' : 'user');
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
