<?php

namespace App\Action\Poa;

use App\Action\AbstractModelAction;
use App\Form\Poa;
use App\Service\Claim as ClaimService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PoaSiriusNoneFoundAction
 * @package App\Action\Poa
 */
class PoaNoneFoundAction extends AbstractModelAction
{
    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * PoaSiriusNoneFoundAction constructor.
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
        $system = $request->getAttribute('system');

        switch ($system) {
            case Poa::SYSTEM_SIRIUS:
                $this->claimService->setNoSiriusPoas($this->modelId, true);
                break;
            case Poa::SYSTEM_MERIS:
                $this->claimService->setNoMerisPoas($this->modelId, true);
                break;
        }

        return $this->redirectToRoute('claim', ['id' => $this->modelId]);
    }
}