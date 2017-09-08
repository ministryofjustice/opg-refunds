<?php

namespace App\Action;

use App\Entity\Cases\Caseworker;
use App\Entity\Cases\RefundCase;
use App\Entity\Sirius\Poa as SiriusPoa;
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
    private $casesEntityManager;

    /**
     * @var EntityManager
     */
    private $siriusEntityManager;

    public function __construct(EntityManager $casesEntityManager, EntityManager $siriusEntityManager)
    {
        $this->casesEntityManager = $casesEntityManager;
        $this->siriusEntityManager = $siriusEntityManager;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $caseDbConnectionSuccessful = false;
        $foundCaseworker = false;
        try {
            $productRepository = $this->casesEntityManager->getRepository(Caseworker::class);
            $caseworkers = $productRepository->findBy([], null, 1);
            $caseDbConnectionSuccessful = true;
            foreach ($caseworkers as $caseworker) {
                /** @var Caseworker $caseworker */
                $foundCaseworker = $caseworker->getId() > 0;
            }
        } catch (Exception $ex) {
            $caseDbConnectionSuccessful = $ex->getMessage();
        }

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

        $siriusDbConnectionSuccessful = false;
        $foundSiriusPoa = false;
        try {
            $productRepository = $this->siriusEntityManager->getRepository(SiriusPoa::class);
            $poas = $productRepository->findBy([], null, 1);
            $siriusDbConnectionSuccessful = true;
            foreach ($poas as $poa) {
                /** @var SiriusPoa $poa */
                $foundSiriusPoa = $poa->getId() > 0;
            }
        } catch (Exception $ex) {
            $siriusDbConnectionSuccessful = $ex->getMessage();
        }

        return new JsonResponse([
            'ack' => time(),
            'caseDbConnectionSuccessful' => $caseDbConnectionSuccessful,
            'foundCaseworker' => $foundCaseworker,
            'foundCase' => $foundCase,
            'siriusDbConnectionSuccessful' => $siriusDbConnectionSuccessful,
            'foundSiriusPoa' => $foundSiriusPoa
        ]);
    }
}
