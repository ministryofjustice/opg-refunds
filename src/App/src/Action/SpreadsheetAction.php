<?php

namespace App\Action;

use App\Service\Spreadsheet;
use App\Spreadsheet\ISpreadsheetGenerator;
use App\Spreadsheet\ISpreadsheetWorksheetGenerator;
use App\Spreadsheet\SpreadsheetFileNameFormatter;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

/**
 * Class SpreadsheetAction
 * @package App\Action
 */
class SpreadsheetAction implements ServerMiddlewareInterface
{
    /**
     * @var Spreadsheet
     */
    private $spreadsheetService;

    /**
     * @var ISpreadsheetWorksheetGenerator
     */
    private $spreadsheetWorksheetGenerator;

    /**
     * @var ISpreadsheetGenerator
     */
    private $spreadsheetGenerator;

    /**
     * SpreadsheetAction constructor
     *
     * @param Spreadsheet $spreadsheetService
     * @param ISpreadsheetWorksheetGenerator $spreadsheetWorksheetGenerator
     * @param ISpreadsheetGenerator $spreadsheetGenerator
     */
    public function __construct(Spreadsheet $spreadsheetService, ISpreadsheetWorksheetGenerator $spreadsheetWorksheetGenerator, ISpreadsheetGenerator $spreadsheetGenerator)
    {
        $this->spreadsheetService = $spreadsheetService;
        $this->spreadsheetWorksheetGenerator = $spreadsheetWorksheetGenerator;
        $this->spreadsheetGenerator = $spreadsheetGenerator;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return Response
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claims = $this->spreadsheetService->getAllRefundable();

        $spreadsheetWorksheet = $this->spreadsheetWorksheetGenerator->generate($claims);

        $schema = ISpreadsheetGenerator::SCHEMA_SSCL;
        $fileFormat = ISpreadsheetGenerator::FILE_FORMAT_XLS;

        $stream = new Stream($this->spreadsheetGenerator->generateStream($schema, $fileFormat, $spreadsheetWorksheet));
        $fileName = SpreadsheetFileNameFormatter::getFileName($schema, $fileFormat);

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
