<?php

namespace App\Action;

use App\Service\Cases;
use App\Spreadsheet\PhpSpreadsheetGenerator;
use App\Spreadsheet\SsclWorksheetGenerator;
use Interop\Container\ContainerInterface;

class SpreadsheetActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        $sourceFolder = $config['spreadsheet']['source_folder'];
        $tempFolder = $config['spreadsheet']['temp_folder'];

        $spreadsheetWorksheetGenerator = new SsclWorksheetGenerator();
        $spreadsheetGenerator = new PhpSpreadsheetGenerator($sourceFolder, $tempFolder);

        return new SpreadsheetAction(
            $container->get(Cases::class),
            $spreadsheetWorksheetGenerator,
            $spreadsheetGenerator
        );
    }
}
