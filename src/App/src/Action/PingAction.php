<?php

namespace App\Action;

use App\Entity\Auth\Caseworker;
use App\Entity\Cases\RefundCase;
use Doctrine\ORM\EntityManager;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class PingAction implements ServerMiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $foundCaseworker = false;
        $productRepository = $this->entityManager->getRepository(Caseworker::class);
        $caseWorkers = $productRepository->findBy([], null, 1);
        foreach ($caseWorkers as $caseWorker) {
            /** @var Caseworker $caseWorker */
            $foundCaseworker = $caseWorker->getId() > 0;
        }

        $foundCase = false;
        $productRepository = $this->entityManager->getRepository(RefundCase::class);
        $cases = $productRepository->findBy([], null, 1);
        foreach ($cases as $case) {
            /** @var RefundCase $case */
            $foundCase = $case->getId() > 0;
        }

        return new JsonResponse([
            'ack' => time(),
            'foundCaseworker' => $foundCaseworker,
            'foundCase' => $foundCase
        ]);
    }
}
