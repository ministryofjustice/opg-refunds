<?php

namespace App\Action;

use DateTime;
use Doctrine\ORM\EntityManager;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class StatsAction
 * @package App\Action
 */
class StatsAction extends AbstractRestfulAction
{
    /**
     * @var EntityManager
     */
    private $claimsEntityManager;

    public function __construct(EntityManager $claimsEntityManager)
    {
        $this->claimsEntityManager = $claimsEntityManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface|Response
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $generated = new DateTime();

        return new JsonResponse([
            'generated' => date('d/m/Y H:i:s', $generated->getTimestamp())
        ]);
    }
}