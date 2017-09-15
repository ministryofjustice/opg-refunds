<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Caseworker;
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
        /** @var Caseworker $identity */
        $identity = $request->getAttribute('identity');

        //  Even though the caseworker details are in the session get them again with a GET call to the API
        $caseworkerData = $this->getApiClient()->getCaseworker($identity->getId());
        $caseworker = new Caseworker($caseworkerData);

        $cases = $this->getApiClient()->getCases();

        return new HtmlResponse($this->getTemplateRenderer()->render('app::home-page', [
            'caseworker' => $caseworker,
            'cases' => $cases,
        ]));
    }
}
