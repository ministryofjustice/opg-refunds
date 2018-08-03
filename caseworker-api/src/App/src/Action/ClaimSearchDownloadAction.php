<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use App\Spreadsheet\ISpreadsheetGenerator;
use App\Spreadsheet\SpreadsheetFileNameFormatter;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

/**
 * Class ClaimSearchDownloadAction
 * @package App\Action
 */
class ClaimSearchDownloadAction extends AbstractRestfulAction
{
    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * @var ISpreadsheetGenerator
     */
    private $spreadsheetGenerator;

    public function __construct(ClaimService $claimService, ISpreadsheetGenerator $spreadsheetGenerator)
    {
        $this->claimService = $claimService;
        $this->spreadsheetGenerator = $spreadsheetGenerator;
    }

    /**
     * READ/GET index action
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request)
    {
        $queryParameters = $request->getQueryParams();

        $fileFormat = ISpreadsheetGenerator::FILE_FORMAT_XLSX;
        $claimSummaries = $this->claimService->searchAll($queryParameters);
        $claimSearchStream = $this->spreadsheetGenerator->getClaimSearchStream(
            $fileFormat,
            $claimSummaries,
            $queryParameters
        );

        $stream = new Stream($claimSearchStream);
        $fileName = SpreadsheetFileNameFormatter::getClaimSearchFileName($fileFormat);

        $response = new Response();

        return $response
            ->withHeader('Content-Type', 'application/vnd.ms-excel')
            ->withHeader(
                'Content-Disposition',
                "attachment; filename=" . basename($fileName)
            )
            ->withHeader('Content-Transfer-Encoding', 'Binary')
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Pragma', 'public')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withBody($stream)
            ->withHeader('Content-Length', "{$stream->getSize()}");
    }
}
