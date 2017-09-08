<?php

namespace App\Action;

use App\Exception\InvalidInputException;
use App\Service\Caseworker as CaseworkerService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class CaseworkerAction
 * @package App\Action
 */
class CaseworkerAction implements ServerMiddlewareInterface
{
    /**
     * @var CaseworkerService
     */
    private $caseworkerService;

    /**
     * CaseworkerAction constructor
     *
     * @param CaseworkerService $caseworkerService
     */
    public function __construct(CaseworkerService $caseworkerService)
    {
        $this->caseworkerService = $caseworkerService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return JsonResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $caseworkerId = $request->getAttribute('id');

        if (is_numeric($caseworkerId)) {
            $caseworker = $this->caseworkerService->findById($caseworkerId);

            return new JsonResponse($caseworker->toArray());
        }

        throw new InvalidInputException('Caseworker not found');
    }
}
