<?php

namespace App\Action;

use App\Service\ClaimService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ClaimAction
 * @package App\Action
 */
class ClaimAction extends AbstractAction
{
    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * ClaimAction constructor.
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
        $claimId = $request->getAttribute('id');
        $userId = $request->getAttribute('identity')->getId();

        $claim = $this->claimService->getClaim($claimId, $userId);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-page', [
            'claim'  => $claim
        ]));
    }
}