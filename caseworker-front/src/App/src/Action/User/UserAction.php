<?php

namespace App\Action\User;

use App\Form\AccountSetUp;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;

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
        if (!is_null($this->modelId)) {
            //  Get the specific user
            $user = $this->userService->getUser($this->modelId);

            $form = $this->getForm($request);

            $form->bind($user);

            return new HtmlResponse($this->getTemplateRenderer()->render('app::user-page', [
                'user'     => $user,
                'form'     => $form,
                'messages' => $this->getFlashMessages($request)
            ]));
        }

        //  Get all users
        $userSummaryPage = $this->userService->searchUsers();

        return new HtmlResponse($this->getTemplateRenderer()->render('app::users-page', [
            'userSummaryPage' => $userSummaryPage,
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|\Zend\Diactoros\Response\RedirectResponse
     * @throws Exception
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        $userId = null;

        if ($form->isValid()) {
            $data = $form->getData();
            $userId = $data['id'];

            $message = 'Set up email resent';

            try {
                $user = $this->userService->refreshSetUpToken($userId);

                $this->sendAccountSetUpEmail($request, $user);
            } catch (Exception $ex) {
                $message = 'Set up email error';
            }

            $this->setFlashMessage($request, 'info', $message);
        }

        return $this->redirectToRoute('user', ['id' => $userId]);
    }

    /**
     * Get the form for the model concerned
     *
     * @param ServerRequestInterface $request
     * @return AccountSetUp
     */
    private function getForm(ServerRequestInterface $request)
    {
        $session = $request->getAttribute('session');

        $form = new AccountSetUp([
            'csrf' => $session['meta']['csrf']
        ]);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());
        }

        return $form;
    }
}
