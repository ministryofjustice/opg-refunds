<?php

namespace App\Action;

use App\Service\Spreadsheet;
use App\Spreadsheet\ISpreadsheetGenerator;
use App\Spreadsheet\ISpreadsheetWorksheetGenerator;
use App\Spreadsheet\SpreadsheetFileNameFormatter;
use DateTime;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Stream;

/**
 * Class SpreadsheetAction
 * @package App\Action
 */
class SpreadsheetAction extends AbstractRestfulAction
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
     * @return ResponseInterface|Response
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $dateString = $request->getAttribute('date');

        if ($dateString === null) {
            // Get all historic refund dates
            $historicRefundDates = $this->spreadsheetService->getAllHistoricRefundDates();
            return new JsonResponse($historicRefundDates);
        } else {
            $identity = $request->getAttribute('identity');

            $date = new DateTime($dateString);
            $claims = $this->spreadsheetService->getAllRefundable(clone $date, $identity->getId());

            $spreadsheetWorksheet = $this->spreadsheetWorksheetGenerator->generate(clone $date, $claims, $identity);

            unset($claims);

            $spreadsheetHashes = $this->spreadsheetWorksheetGenerator->getHashes($spreadsheetWorksheet);

            $this->spreadsheetService->storeSpreadsheetHashes($spreadsheetHashes);

            unset($spreadsheetHashes);

            $schema = ISpreadsheetGenerator::SCHEMA_SSCL;
            $fileFormat = ISpreadsheetGenerator::FILE_FORMAT_XLS;

            $stream = new Stream($this->spreadsheetGenerator->generateStream($schema, $fileFormat, $spreadsheetWorksheet));
            $fileName = SpreadsheetFileNameFormatter::getFileName($schema, $fileFormat, $dateString);

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

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface|Response
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        // Retrieve spreadsheet file
        $worksheetData = $this->spreadsheetGenerator->getWorksheetData($request->getBody());
        $worksheet = $this->spreadsheetWorksheetGenerator->getWorksheet($worksheetData);

        $date = false;
        foreach ($worksheet->getRows() as $row) {
            $date = DateTime::createFromFormat('d/m/Y', $row->getCells()[16]->getData());

            if ($date === false) {
                continue;
            } else {
                $date->setTime(0, 0, 0);
                break;
            }
        }

        if ($date === false) {
            return new JsonResponse([
                'valid' => false,
                'added' => [],
                'changed' => [],
                'deleted' => []
            ]);
        }

        $spreadsheetHashes = $this->spreadsheetWorksheetGenerator->getHashes($worksheet);

        $result = $this->spreadsheetService->validateSpreadsheetHashes($spreadsheetHashes, $date);

        return new JsonResponse($result);
    }
}
