<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use App\Spreadsheet\PhpSpreadsheetGenerator;
use Interop\Container\ContainerInterface;
use Zend;

/**
 * Class ClaimSearchDownloadActionFactory
 * @package App\Action
 */
class ClaimSearchDownloadActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimSearchDownloadAction
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        $sourceFolder = $config['spreadsheet']['source_folder'];
        $tempFolder = $config['spreadsheet']['temp_folder'];

        $spreadsheetGenerator = new PhpSpreadsheetGenerator($sourceFolder, $tempFolder);
        $spreadsheetGenerator->setLogger($container->get(\Laminas\Log\Logger::class));

        return new ClaimSearchDownloadAction(
            $container->get(ClaimService::class),
            $spreadsheetGenerator
        );
    }
}
