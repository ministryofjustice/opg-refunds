<?php

namespace App\Action;

use App\Service\Notify as NotifyService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;

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
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $this->notifyService->notifyAll();
    }
}