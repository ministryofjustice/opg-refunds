<?php

namespace App\Action;

use App\DataModel\Applications\Application;
use App\DataModel\Cases\RefundCase as CaseDataModel;
use App\Entity\Cases\RefundCase as CaseEntity;
use App\Service\Cases;
use App\Spreadsheet\ISpreadsheetGenerator;
use App\Spreadsheet\ISpreadsheetWorksheetGenerator;
use App\Spreadsheet\SpreadsheetFileNameFormatter;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Crypt\PublicKey\Rsa;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class SpreadsheetAction implements ServerMiddlewareInterface
{
    /**
     * @var Cases
     */
    private $casesService;

    /**
     * @var Rsa
     */
    private $bankCipher;

    /**
     * @var ISpreadsheetWorksheetGenerator
     */
    private $spreadsheetWorksheetGenerator;

    /**
     * @var ISpreadsheetGenerator
     */
    private $spreadsheetGenerator;

    public function __construct(
        Cases $casesService,
        Rsa $bankCipher,
        ISpreadsheetWorksheetGenerator $spreadsheetWorksheetGenerator,
        ISpreadsheetGenerator $spreadsheetGenerator
    ) {
        $this->casesService = $casesService;
        $this->bankCipher = $bankCipher;
        $this->spreadsheetWorksheetGenerator = $spreadsheetWorksheetGenerator;
        $this->spreadsheetGenerator = $spreadsheetGenerator;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $cases = $this->casesService->getAllRefundable($this->bankCipher);

        $spreadsheetWorksheet = $this->spreadsheetWorksheetGenerator->generate($cases);

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
