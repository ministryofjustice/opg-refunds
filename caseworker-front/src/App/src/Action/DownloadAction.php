<?php

namespace App\Action;

use App\Service\Refund\Refund as RefundService;
use DateTime;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

/**
 * Class DownloadAction
 * @package App\Action
 */
class DownloadAction extends AbstractAction
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
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $dateString = $request->getAttribute('date');
        $date = $dateString === null ? new DateTime('today') : new DateTime($dateString);

        $refundSpreadsheet = $this->refundService->getRefundSpreadsheet($date);

        $response = new Response();

        return $response
            ->withHeader('Content-Type', 'application/vnd.ms-excel')
            ->withHeader(
                'Content-Disposition',
                "attachment; filename=" . basename($refundSpreadsheet['name'])
            )
            ->withHeader('Content-Transfer-Encoding', 'Binary')
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Pragma', 'public')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withBody($refundSpreadsheet['stream'])
            ->withHeader('Content-Length', $refundSpreadsheet['length']);
    }
}
