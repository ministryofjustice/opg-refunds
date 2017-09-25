<?php

namespace App\Action\Poa;

use App\Action\AbstractModelAction;
use App\Service\Claim as ClaimService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PoaMerisCancelNoneFoundAction
 * @package App\Action\Poa
 */
class PoaMerisCancelNoneFoundAction extends AbstractModelAction
{
    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * PoaMerisCancelNoneFoundAction constructor.
     * @param ClaimService $claimService
     */
    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $this->claimService->setNoMerisPoas($this->modelId, false);

        return $this->redirectToRoute('claim', ['id' => $this->modelId]);
    }
}