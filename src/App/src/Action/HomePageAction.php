<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Caseworker;
use Opg\Refunds\Caseworker\DataModel\Cases\RefundCase;
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

        $refundCases = [];

        $refundCasesData = $this->getApiClient()->getRefundCases();

        foreach ($refundCasesData as $refundCaseData) {
            $refundCases[] = new RefundCase($refundCaseData);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::home-page', [
            'caseworker'  => $caseworker,
            'refundCases' => $refundCases,
        ]));
    }
}
