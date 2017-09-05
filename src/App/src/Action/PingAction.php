<?php

namespace App\Action;

use App\Entity\Auth\Caseworker;
use App\Entity\Cases\RefundCase;
use Doctrine\ORM\EntityManager;
use Exception;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class PingAction implements ServerMiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $authEntityManager;

    /**
     * @var EntityManager
     */
    private $casesEntityManager;

    public function __construct(EntityManager $authEntityManager, EntityManager $casesEntityManager)
    {
        $this->authEntityManager = $authEntityManager;
        $this->casesEntityManager = $casesEntityManager;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $authDbConnectionSuccessful = false;
        $foundCaseworker = false;
        try {
            $productRepository = $this->authEntityManager->getRepository(Caseworker::class);
            $caseWorkers = $productRepository->findBy([], null, 1);
            $authDbConnectionSuccessful = true;
            foreach ($caseWorkers as $caseWorker) {
                /** @var Caseworker $caseWorker */
                $foundCaseworker = $caseWorker->getId() > 0;
            }
        } catch (Exception $ex) {
            $authDbConnectionSuccessful = $ex->getMessage();
        }

        $caseDbConnectionSuccessful = false;
        $foundCase = false;
        try {
            $productRepository = $this->casesEntityManager->getRepository(RefundCase::class);
            $cases = $productRepository->findBy([], null, 1);
            $caseDbConnectionSuccessful = true;
            foreach ($cases as $case) {
                /** @var RefundCase $case */
                $foundCase = $case->getId() > 0;
            }
        } catch (Exception $ex) {
            $caseDbConnectionSuccessful = $ex->getMessage();
        }


        return new JsonResponse([
            'ack' => time(),
            'authDbConnectionSuccessful' => $authDbConnectionSuccessful,
            'foundCaseworker' => $foundCaseworker,
            'caseDbConnectionSuccessful' => $caseDbConnectionSuccessful,
            'foundCase' => $foundCase
        ]);
    }
}
