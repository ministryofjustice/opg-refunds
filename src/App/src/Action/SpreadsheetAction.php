<?php

namespace App\Action;

use App\Service\Spreadsheet;
use App\Service\User as UserService;
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
     * @var UserService
     */
    private $userService;

    /**
     * SpreadsheetAction constructor
     *
     * @param Spreadsheet $spreadsheetService
     * @param ISpreadsheetWorksheetGenerator $spreadsheetWorksheetGenerator
     * @param ISpreadsheetGenerator $spreadsheetGenerator
     */
    public function __construct(Spreadsheet $spreadsheetService, ISpreadsheetWorksheetGenerator $spreadsheetWorksheetGenerator, ISpreadsheetGenerator $spreadsheetGenerator, UserService $userService)
    {
        $this->spreadsheetService = $spreadsheetService;
        $this->spreadsheetWorksheetGenerator = $spreadsheetWorksheetGenerator;
        $this->spreadsheetGenerator = $spreadsheetGenerator;
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface|Response
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $dateString = $request->getAttribute('id');

        if ($dateString === null) {
            // Get all historic refund dates
            $historicRefundDates = $this->spreadsheetService->getAllHistoricRefundDates();
            return new JsonResponse($historicRefundDates);
        } else {
            $token = $request->getHeaderLine('token');
            $user = $this->userService->getByToken($token);

            $claims = $this->spreadsheetService->getAllRefundable(new DateTime($dateString), $user->getId());

            $spreadsheetWorksheet = $this->spreadsheetWorksheetGenerator->generate($claims);

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
}
