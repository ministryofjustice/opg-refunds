<?php

namespace App\Action;

use App\Service\Spreadsheet;
use App\Service\User as UserService;
use App\Spreadsheet\PhpSpreadsheetGenerator;
use App\Spreadsheet\SsclWorksheetGenerator;
use Interop\Container\ContainerInterface;

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

        $spreadsheetWorksheetGenerator = new SsclWorksheetGenerator($config['spreadsheet']['sscl']);
        $spreadsheetGenerator = new PhpSpreadsheetGenerator($sourceFolder, $tempFolder);

        return new SpreadsheetAction(
            $container->get(Spreadsheet::class),
            $spreadsheetWorksheetGenerator,
            $spreadsheetGenerator,
            $container->get(UserService::class)
        );
    }
}
