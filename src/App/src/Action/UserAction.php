<?php

namespace App\Action;

use App\Exception\InvalidInputException;
use App\Service\Caseworker as CaseworkerService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class UserAction
 * @package App\Action
 */
class UserAction implements ServerMiddlewareInterface
{
    /**
     * @var CaseworkerService
     */
    private $caseworkerService;

    /**
     * UserAction constructor
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

            //  TODO - Implement a more elegant way of translating entity data into an JSON response
            return new JsonResponse([
                'id'     => $caseworker->getId(),
                'name'   => $caseworker->getName(),
                'email'  => $caseworker->getEmail(),
                'status' => $caseworker->getStatus(),
                'roles'  => $caseworker->getRoles(),
                'token'  => $caseworker->getToken(),
            ]);
        }

        throw new InvalidInputException('User not found');
    }
}
