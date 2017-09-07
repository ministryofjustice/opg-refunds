<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
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
        $identity = $request->getAttribute('identity');

        //  Even though the details are in the session get them again with a GET call to the API
        $user = $this->getApiClient()->getUser($identity['id']);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::home-page', [
            'caseworker' => $user,
        ]));
    }
}
