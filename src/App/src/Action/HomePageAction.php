<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class HomePageAction
 * @package App\Action
 */
class HomePageAction extends AbstractAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  Get the name from the identity for display
        $identity = $request->getAttribute('identity');

        return new HtmlResponse($this->getTemplateRenderer()->render('app::home-page', [
            'name' => $identity->name,
        ]));
    }
}
