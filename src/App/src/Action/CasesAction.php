<?php

namespace App\Action;

use App\Service\Cases;
use Applications\Service\DataMigration;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class CasesAction implements ServerMiddlewareInterface
{
    /**
     * @var Cases
     */
    private $casesService;

    /**
     * @var DataMigration
     */
    private $dataMigrationService;

    public function __construct(Cases $casesService, DataMigration $dataMigrationService)
    {
        $this->casesService = $casesService;
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

        //TODO: Paging
        $cases = $this->casesService->getAllEntitiesAsArray();

        return new JsonResponse($cases);
    }
}