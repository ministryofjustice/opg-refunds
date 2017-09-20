<?php

namespace App\Action;

use App\Service\RefundCaseService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ProcessNewClaimAction
 * @package App\Action
 */
class ProcessNewClaimAction extends AbstractAction
{
    /**
     * @var RefundCaseService
     */
    private $refundCaseService;

    /**
     * ProcessNewClaimAction constructor.
     * @param RefundCaseService $refundCaseService
     */
    public function __construct(RefundCaseService $refundCaseService)
    {
        $this->refundCaseService = $refundCaseService;
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
        $refundCase = $this->refundCaseService->getNextRefundCase();

        return $this->redirectToRoute('home');
    }
}