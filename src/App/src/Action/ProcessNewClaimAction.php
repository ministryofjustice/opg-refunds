<?php

namespace App\Action;

use App\Service\ClaimService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;

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
        $userId = $request->getAttribute('identity')->getId();
        $assignedClaimId = $this->claimService->assignNextClaim($userId);

        if ($assignedClaimId === 0) {
            //No available claims

            /** @var Messages $flash */
            $flash = $request->getAttribute('flash');
            $flash->addMessage('info', 'There are no more claims to process');

            return $this->redirectToRoute('home');
        }

        return $this->redirectToRoute('claim', ['id' => $assignedClaimId]);
    }
}