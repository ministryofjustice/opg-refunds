<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Applications\Service\DataMigration;
use Fig\Http\Message\RequestMethodInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class CaseworkerClaimAction
 * @package App\Action
 */
class CaseworkerClaimAction implements ServerMiddlewareInterface
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
        $claimId = $request->getAttribute('id');

        $method = $request->getMethod();
        switch ($method) {
            case RequestMethodInterface::METHOD_GET:
                return $this->getClaim($claimId);
            default:
                throw new InvalidArgumentException("Request method $method is not valid for this endpoint");
        }
    }

    /**
     * @param $claimId if supplied, return corresponding claim, if null attempt to reserve and return claim for logged in user
     * @return JsonResponse
     */
    private function getClaim($claimId)
    {
        if ($claimId == null) {
            //TODO: Get proper migration running via cron job
            $this->dataMigrationService->migrateOne();



            //Retrieve next claim and return if found
            return new JsonResponse([]);
        } else {
            //Retrieve specific claim
            return new JsonResponse([]);
        }
    }
}