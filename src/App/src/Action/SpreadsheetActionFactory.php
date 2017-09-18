<?php

namespace App\Action;

use App\Service\RefundCase as RefundCaseService;
use App\Spreadsheet\PhpSpreadsheetGenerator;
use App\Spreadsheet\SsclWorksheetGenerator;
use Interop\Container\ContainerInterface;
use Zend\Crypt\PublicKey\Rsa;

/**
 * Class SpreadsheetActionFactory
 * @package App\Action
 */
class SpreadsheetActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return SpreadsheetAction
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        $sourceFolder = $config['spreadsheet']['source_folder'];
        $tempFolder = $config['spreadsheet']['temp_folder'];

        $spreadsheetWorksheetGenerator = new SsclWorksheetGenerator();
        $spreadsheetGenerator = new PhpSpreadsheetGenerator($sourceFolder, $tempFolder);

        return new SpreadsheetAction(
            $container->get(RefundCaseService::class),
            $spreadsheetWorksheetGenerator,
            $spreadsheetGenerator
        );
    }
}
