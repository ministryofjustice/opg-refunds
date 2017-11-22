<?php

namespace App\Action;

use App\Service\Notify as NotifyService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class NotifyAction
 * @package App\Action
 */
class NotifyAction extends AbstractRestfulAction
{
    /**
     * @var NotifyService
     */
    private $notifyService;

    public function __construct(NotifyService $notifyService)
    {
        $this->notifyService = $notifyService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $notified = $this->notifyService->notifyAll();

        return new JsonResponse($notified);
    }
}