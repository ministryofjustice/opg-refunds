<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Applications\Service\DataMigration;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class ClaimAction
 * @package App\Action
 */
class ClaimAction implements ServerMiddlewareInterface
{
    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * @var DataMigration
     */
    private $dataMigrationService;

    public function __construct(ClaimService $claimService, DataMigration $dataMigrationService)
    {
        $this->claimService = $claimService;
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
        $claims = $this->claimService->getAll();
        $claimsData = [];

        foreach ($claims as $claim) {
            $claimsData[] = $claim->toArray();
        }

        return new JsonResponse($claimsData);
    }
}