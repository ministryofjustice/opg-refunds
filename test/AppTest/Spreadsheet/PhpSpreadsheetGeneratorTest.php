<?php

namespace AppTest\Spreadsheet;

use Opg\Refunds\Caseworker\DataModel\Applications\Account;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use App\Spreadsheet\ISpreadsheetGenerator;
use App\Spreadsheet\PhpSpreadsheetGenerator;
use App\Spreadsheet\SpreadsheetWorksheet;
use App\Spreadsheet\SsclWorksheetGenerator;
use AppTest\DataModel\Applications\ApplicationBuilder;
use AppTest\DataModel\Cases\ClaimBuilder;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use PHPUnit\Framework\TestCase;

class PhpSpreadsheetGeneratorTest extends TestCase
{
    private $sourceFolder = __DIR__ . '/../../../assets';
    private $tempFolder = __DIR__ . '/../output';
    /**
     * @var array
     */
    private $ssclConfig = [
        'entity' => '0123',
        'cost_centre' => '99999999',
        'account' => '123450000',
        'objective' => '0',
        'analysis' => '12345678',
        'completer_id' => 'completer@localhost.com',
        'approver_id' => 'approver@localhost.com',
    ];
    /**
     * @var ISpreadsheetGenerator
     */
    private $spreadsheetGenerator;
    /**
     * @var SpreadsheetWorksheet
     */
    private $worksheet;
    /**
     * @var array
     */
    private $filesToDelete = [];

    public static function setUpBeforeClass()
    {
        $filename = __DIR__ . '/../output/UnitTest.xls';
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public function setUp()
    {
        $this->spreadsheetGenerator = new PhpSpreadsheetGenerator($this->sourceFolder, $this->tempFolder);

        $claimBuilder = new ClaimBuilder();
        $applicationBuilder = new ApplicationBuilder();

        $account = new Account();
        $account
            ->setName('Mr Unit Test')
            ->setAccountNumber('12345678')
            ->setSortCode('112233');

        $application = $applicationBuilder->withAccount($account)->build();

        $payment = new Payment();
        $payment->setAmount(45);

        $claim = $claimBuilder
            ->withApplication($application)
            ->withPayment($payment)
            ->build();

        $spreadsheetWorksheetGenerator = new SsclWorksheetGenerator($this->ssclConfig);
        $this->worksheet = $spreadsheetWorksheetGenerator->generate([$claim]);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Supplied schema and file format is not supported
     */
    public function testSchemaFileFormatNotSupported()
    {
        $this->spreadsheetGenerator->generateFile(
            ISpreadsheetGenerator::SCHEMA_SSCL,
            ISpreadsheetGenerator::FILE_FORMAT_XLSX,
            'UnitTest.xls',
            $this->worksheet
        );
    }

    public function testSchemaSsclFileFormatXlsOneRow()
    {
        $result = $this->spreadsheetGenerator->generateFile(
            ISpreadsheetGenerator::SCHEMA_SSCL,
            ISpreadsheetGenerator::FILE_FORMAT_XLS,
            'UnitTest.xls',
            $this->worksheet
        );

        $this->assertNotNull($result);
        $this->assertEquals($this->tempFolder . '/UnitTest.xls', $result);
        $this->assertTrue(file_exists($result));

        //Load and check spreadsheet data
        $reader = new XlsReader();
        $ssclSourceSpreadsheet = $reader->load($result);

        $dataSheet = $ssclSourceSpreadsheet->getSheetByName($this->worksheet->getName());

        foreach ($this->worksheet->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                $this->assertEquals(
                    $cell->getData(),
                    $dataSheet->getCellByColumnAndRow(
                        $cell->getColumn(),
                        $cell->getRow()
                    )->getValue()
                );
            }
        }
    }

    /**
     * @depends testSchemaSsclFileFormatXlsOneRow
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Supplied filename already exists in temp folder
     */
    public function testFileAlreadyExists()
    {
        $this->spreadsheetGenerator->generateFile(
            ISpreadsheetGenerator::SCHEMA_SSCL,
            ISpreadsheetGenerator::FILE_FORMAT_XLS,
            'UnitTest.xls',
            $this->worksheet
        );
    }

    /**
     * @depends testFileAlreadyExists
     */
    public function testDeleteTempFiles()
    {
        $this->assertTrue(file_exists($this->tempFolder . '/UnitTest.xls'));

        $this->spreadsheetGenerator->deleteTempFiles();

        $this->assertFalse(file_exists($this->tempFolder . '/UnitTest.xls'));
    }

    /**
     * @depends testDeleteTempFiles
     */
    public function testGenerateStream()
    {
        $result = $this->spreadsheetGenerator->generateStream(
            ISpreadsheetGenerator::SCHEMA_SSCL,
            ISpreadsheetGenerator::FILE_FORMAT_XLS,
            $this->worksheet
        );

        $this->assertNotNull($result);
        $this->assertNotFalse($result);

        //Save stream to file for testing
        $fileName = $this->tempFolder . '/StreamUnitTest.xls';
        file_put_contents($fileName, $result);

        $this->assertTrue(file_exists($fileName));

        //Load and check spreadsheet data
        $reader = new XlsReader();
        $ssclSourceSpreadsheet = $reader->load($fileName);

        $dataSheet = $ssclSourceSpreadsheet->getSheetByName($this->worksheet->getName());

        foreach ($this->worksheet->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                $this->assertEquals(
                    $cell->getData(),
                    $dataSheet->getCellByColumnAndRow(
                        $cell->getColumn(),
                        $cell->getRow()
                    )->getValue()
                );
            }
        }

        $this->spreadsheetGenerator->deleteTempFiles();
    }

    public function tearDown()
    {
        foreach ($this->filesToDelete as $fileToDelete) {
            unlink($fileToDelete);
        }
    }
}
