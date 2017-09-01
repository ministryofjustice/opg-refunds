<?php

namespace App\Action;

use App\Spreadsheet\ISpreadsheetGenerator;
use App\Spreadsheet\ISpreadsheetWorksheetGenerator;
use App\Spreadsheet\SpreadsheetFileNameFormatter;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class SpreadsheetAction implements ServerMiddlewareInterface
{
    /**
     * @var ISpreadsheetWorksheetGenerator
     */
    private $spreadsheetWorksheetGenerator;
    /**
     * @var ISpreadsheetGenerator
     */
    private $spreadsheetGenerator;

    public function __construct(
        ISpreadsheetWorksheetGenerator $spreadsheetWorksheetGenerator,
        ISpreadsheetGenerator $spreadsheetGenerator
    )
    {
        $this->spreadsheetWorksheetGenerator = $spreadsheetWorksheetGenerator;
        $this->spreadsheetGenerator = $spreadsheetGenerator;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $data = [
            [
                'payeeName' => 'Mr Unit Test',
                'accountNumber' => '12345678',
                'sortCode' => '112233',
                'amount' => 45,
                'reference' => 'AREFERENCE123'
            ]
        ];

        $spreadsheetWorksheet = $this->spreadsheetWorksheetGenerator->generate($data);

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
