<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use App\Service\User as UserService;
use Exception;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Log as LogModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class ClaimLogAction
 * @package App\Action
 */
class ClaimLogAction extends AbstractRestfulAction
{
    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(ClaimService $claimService, UserService $userService)
    {
        $this->claimService = $claimService;
        $this->userService = $userService;
    }

    /**
     * READ/GET index action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claimId = $request->getAttribute('claimId');
        $claim = $this->claimService->get($claimId);

        $logId = $request->getAttribute('id');
        if ($logId === null) {
            //  Return all of the logs
            $logsData = [];

            foreach ($claim->getLogs() as $log) {
                $logsData[] = $log->toArray();
            }

            return new JsonResponse($logsData);
        } else {
            //  Return a specific log
            foreach ($claim->getLogs() as $log) {
                if ($log->getId() === $logId) {
                    return new JsonResponse($log->toArray());
                }
            }

            return new JsonResponse([]);
        }
    }

    /**
     * CREATE/POST add action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     * @throws Exception
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $requestBody = $request->getParsedBody();
        $log = new LogModel($requestBody);

        $claimId = $request->getAttribute('claimId');

        $token = $request->getHeaderLine('token');
        $user = $this->userService->getByToken($token);

        $log = $this->claimService->addLog($claimId, $user->getId(), $log->getTitle(), $log->getMessage());

        return new JsonResponse($log->toArray());
    }
}