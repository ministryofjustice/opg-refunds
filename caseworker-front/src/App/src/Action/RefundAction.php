<?php

namespace App\Action;

use App\Service\Refund\Refund as RefundService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class RefundAction
 * @package App\Action
 */
class RefundAction extends AbstractAction
{
    /**
     * @var RefundService
     */
    private $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $historicRefundDates = $this->refundService->getHistoricRefundDates();

        return new HtmlResponse($this->getTemplateRenderer()->render('app::refund-page', [
            'historicRefundDates' => $historicRefundDates
        ]));
    }
}
