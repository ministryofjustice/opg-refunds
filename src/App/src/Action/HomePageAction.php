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

        //  Even though the caseworker details are in the session get them again with a GET call to the API
        $caseworker = $this->getApiClient()->getCaseworker($identity['id']);

        $cases = $this->getApiClient()->getCases();

        return new HtmlResponse($this->getTemplateRenderer()->render('app::home-page', [
            'caseworker' => $caseworker,
            'cases' => $cases,
        ]));
    }
}
