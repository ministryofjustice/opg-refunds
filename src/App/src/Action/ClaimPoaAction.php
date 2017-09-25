<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use App\Service\User as UserService;
use Exception;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class ClaimPoaAction
 * @package App\Action
 */
class ClaimPoaAction extends AbstractRestfulAction
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

        $poaId = $request->getAttribute('id');
        if ($poaId === null) {
            //  Return all of the poas
            $poasData = [];

            foreach ($claim->getPoas() as $poa) {
                $poasData[] = $poa->toArray();
            }

            return new JsonResponse($poasData);
        } else {
            //  Return a specific poa
            foreach ($claim->getPoas() as $poa) {
                if ($poa->getId() === $poaId) {
                    return new JsonResponse($poa->toArray());
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
        $poaModel = new PoaModel($requestBody);

        $claimId = $request->getAttribute('claimId');

        $token = $request->getHeaderLine('token');
        $user = $this->userService->getByToken($token);

        $poa = $this->claimService->addPoa($claimId, $user->getId(), $poaModel);

        return new JsonResponse($poa->toArray());
    }
}