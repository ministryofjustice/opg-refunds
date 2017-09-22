<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class HomePageAction
 * @package App\Action
 */
class HomePageAction extends AbstractApiClientAction
{
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
        $userData = $this->getApiClient()->getUser($identity->getId());
        $user = new User($userData);

        $flash = $request->getAttribute('flash');
        $messages = $flash->getMessages();

        return new HtmlResponse($this->getTemplateRenderer()->render('app::home-page', [
            'user'     => $user,
            'messages' => $messages
        ]));
    }
}
