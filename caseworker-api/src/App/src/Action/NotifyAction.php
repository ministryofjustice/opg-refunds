<?php

namespace App\Action;

use App\Service\Notify as NotifyService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

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

    /**
     * @var int
     */
    private $timeout;

    /**
     * NotifyAction constructor.
     * @param NotifyService $notifyService
     * @param array $notifyConfig
     */
    public function __construct(NotifyService $notifyService, array $notifyConfig)
    {
        $this->notifyService = $notifyService;
        $this->timeout = $notifyConfig['timeout'];
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function addAction(ServerRequestInterface $request)
    {
        $identity = $request->getAttribute('identity');

        $notified = $this->notifyService->notifyAll($identity->getId(), $this->timeout);

        return new JsonResponse($notified);
    }
}
