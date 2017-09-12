<?php

namespace App\Action;

use App\Entity\Cases\RefundCase;
use Applications\Service\DataMigration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class CasesAction implements ServerMiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $casesEntityManager;

    /**
     * @var EntityRepository
     */
    private $caseRepository;

    /**
     * @var DataMigration
     */
    private $dataMigrationService;

    public function __construct(EntityManager $casesEntityManager, DataMigration $dataMigrationService)
    {
        $this->casesEntityManager = $casesEntityManager;
        $this->dataMigrationService = $dataMigrationService;
        $this->caseRepository = $this->casesEntityManager->getRepository(RefundCase::class);
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
        $caseArrays = [];

        $cases = $this->caseRepository->findBy([], null);
        foreach ($cases as $case) {
            /** @var RefundCase $case */
            $caseArrays[] = $case->toArray();
        }

        return new JsonResponse($caseArrays);
    }
}