<?php

namespace App\Action;

use App\Service\ClaimService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ProcessNewClaimAction
 * @package App\Action
 */
class ProcessNewClaimAction extends AbstractAction
{
    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * ProcessNewClaimAction constructor.
     * @param ClaimService $claimService
     */
    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->claimService->getNextClaim();

        return $this->redirectToRoute('home');
    }
}