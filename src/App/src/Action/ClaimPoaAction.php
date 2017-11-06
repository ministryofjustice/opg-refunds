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

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
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

        $identity = $request->getAttribute('identity');

        $claim = $this->claimService->get($claimId, $identity->getId());

        $poaId = $request->getAttribute('id');
        if ($poaId === null) {
            //  Return all of the poas
            $poasData = [];

            foreach ($claim->getPoas() as $poa) {
                $poasData[] = $poa->getArrayCopy();
            }

            return new JsonResponse($poasData);
        } else {
            //  Return a specific poa
            foreach ($claim->getPoas() as $poa) {
                if ($poa->getId() === $poaId) {
                    return new JsonResponse($poa->getArrayCopy());
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
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $requestBody = $request->getParsedBody();
        $poaModel = new PoaModel($requestBody);

        $claimId = $request->getAttribute('claimId');

        $identity = $request->getAttribute('identity');

        $claimModel = $this->claimService->addPoa($claimId, $identity->getId(), $poaModel);

        return new JsonResponse($claimModel->getArrayCopy());
    }

    /**
     * UPDATE/PUT edit action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $requestBody = $request->getParsedBody();
        $poaModel = new PoaModel($requestBody);

        $claimId = $request->getAttribute('claimId');
        $poaId = $request->getAttribute('id');

        $identity = $request->getAttribute('identity');

        $claimModel = $this->claimService->editPoa($claimId, $poaId, $identity->getId(), $poaModel);

        return new JsonResponse($claimModel->getArrayCopy());
    }

    /**
     * DELETE/DELETE delete action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function deleteAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claimId = $request->getAttribute('claimId');
        $poaId = $request->getAttribute('id');

        $identity = $request->getAttribute('identity');

        $claimModel = $this->claimService->deletePoa($claimId, $poaId, $identity->getId());

        return new JsonResponse($claimModel->getArrayCopy());
    }
}