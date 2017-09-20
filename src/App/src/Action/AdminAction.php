<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Caseworker;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class AdminAction
 * @package App\Action
 */
class AdminAction extends AbstractApiClientAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  Get all caseworkers
        $caseworkers = [];

        //  Even though the caseworker details are in the session get them again with a GET call to the API
        $caseworkersData = $this->getApiClient()->getCaseworkers();

        foreach ($caseworkersData as $caseworkerData) {
            $caseworkers[] = new Caseworker($caseworkerData);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::admin-page', [
            'caseworkers'  => $caseworkers,
        ]));
    }
}
