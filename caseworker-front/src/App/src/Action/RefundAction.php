<?php

namespace App\Action;

use App\Service\Refund\Refund as RefundService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\HtmlResponse;

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
     * @return HtmlResponse
     */
    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        $historicRefundDates = $this->refundService->getHistoricRefundDates();

        return new HtmlResponse($this->getTemplateRenderer()->render('app::refund-page', [
            'historicRefundDates' => $historicRefundDates
        ]));
    }
}
