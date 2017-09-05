<?php

namespace App\Action;

use App\Entity\RefundCase;
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
        $foundCase = false;

        $productRepository = $this->entityManager->getRepository(RefundCase::class);
        $cases = $productRepository->findBy([], null, 1);
        foreach ($cases as $case) {
            /** @var RefundCase $case */
            $foundCase = $case->getId() > 0;
        }

        return new JsonResponse([
            'ack' => time(),
            'foundCase' => $foundCase
        ]);
    }
}
