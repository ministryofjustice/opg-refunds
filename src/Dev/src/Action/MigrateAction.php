<?php

namespace Dev\Action;

use Applications\Service\DataMigration;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class MigrateAction implements ServerMiddlewareInterface
{
    /**
     * @var DataMigration
     */
    private $dataMigrationService;

    public function __construct(DataMigration $dataMigrationService)
    {
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
        $result = $this->dataMigrationService->migrateOne();

        return new JsonResponse(['migratedOne' => $result]);
    }
}