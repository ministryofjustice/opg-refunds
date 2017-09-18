<?php

namespace App\Action;

use App\Service\RefundCase as RefundCaseService;
use Applications\Service\DataMigration;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class RefundCaseAction
 * @package App\Action
 */
class RefundCaseAction implements ServerMiddlewareInterface
{
    /**
     * @var RefundCaseService
     */
    private $refundCaseService;

    /**
     * @var DataMigration
     */
    private $dataMigrationService;

    public function __construct(RefundCaseService $refundCaseService, DataMigration $dataMigrationService)
    {
        $this->refundCaseService = $refundCaseService;
        $this->dataMigrationService = $dataMigrationService;
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
        //TODO: Get proper migration running via cron job
        $this->dataMigrationService->migrateAll();

        //  Get all of the refund cases
        $refundCases = $this->refundCaseService->getAll();
        $refundCasesData = [];

        foreach ($refundCases as $refundCase) {
            $refundCasesData[] = $refundCase->toArray();
        }

        return new JsonResponse($refundCasesData);
    }
}