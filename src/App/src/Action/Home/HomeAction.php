<?php

namespace App\Action\Home;

use App\Action\AbstractAction;
use App\Form\ProcessNewClaim;
use App\Service\User\User as UserService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class HomeAction
 * @package App\Action\Home
 */
class HomeAction extends AbstractAction
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
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /** @var User $identity */
        $identity = $request->getAttribute('identity');

        //  Even though the user details are in the session get them again with a GET call to the API
        $user = $this->userService->getUser($identity->getId());

        $session = $request->getAttribute('session');
        $form = new ProcessNewClaim([
            'csrf' => $session['meta']['csrf'],
        ]);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::home-page', [
            'form'     => $form,
            'user'     => $user,
            'messages' => $this->getFlashMessages($request)
        ]));
    }
}
