<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use App\Action\AbstractAction;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;

/**
 * Class ClaimSearchDownloadAction
 * @package App\Action\Claim
 */
class ClaimSearchDownloadAction extends AbstractAction
{
    /**
     * @var ClaimService
     */
    protected $claimService;

    /**
     * AbstractClaimAction constructor
     * @param ClaimService $claimService
     */
    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        $searchParameters = $request->getQueryParams();

        $claimSummarySpreadsheet = $this->claimService->getSearchClaimsSpreadsheet($searchParameters);

        $response = new Response();

        return $response
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader(
                'Content-Disposition',
                "attachment; filename=" . basename($claimSummarySpreadsheet['name'])
            )
            ->withHeader('Content-Transfer-Encoding', 'Binary')
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Pragma', 'public')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withBody($claimSummarySpreadsheet['stream'])
            ->withHeader('Content-Length', $claimSummarySpreadsheet['length']);
    }
}
