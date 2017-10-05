<?php

namespace App\Service\Refund;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;
use App\Service\Date\IDate as DateService;
use DateTime;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;

/**
 * Class Refund
 * @package App\Service\Refund
 */
class Refund implements ApiClientInterface
{
    use ApiClientTrait;

    public function getRefundSpreadsheet(DateTime $date)
    {
        $dateString = date('Y-m-d', $date->getTimestamp());

        $response = $this->getApiClient()->httpGetResponse('/v1/cases/spreadsheet/' . $dateString);

        $fileContents = $response->getBody();
        $contentDisposition = $response->getHeaderLine('Content-Disposition');
        $fileName = substr($contentDisposition, strpos($contentDisposition, '=') + 1);
        $contentLength = $response->getHeaderLine('Content-Length');

        return [
            'stream' => $fileContents,
            'name'   => $fileName,
            'length' => $contentLength
        ];
    }
}