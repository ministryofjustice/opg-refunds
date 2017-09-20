<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class HomeRefundAction
 * @package App\Action
 */
class HomeRefundAction extends AbstractAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return new HtmlResponse($this->getTemplateRenderer()->render('app::home-refund-page'));
    }
}
