<?php

namespace App\Service\Refund;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;
use DateTime;
use Zend\Diactoros\UploadedFile;

/**
 * Class Refund
 * @package App\Service\Refund
 */
class Refund implements ApiClientInterface
{
    use ApiClientTrait;

    public function getHistoricRefundDates()
    {
        $historicRefundDates = $this->getApiClient()->httpGet('/v1/spreadsheet');

        return $historicRefundDates;
    }

    public function getRefundSpreadsheet(DateTime $date)
    {
        $dateString = date('Y-m-d', $date->getTimestamp());

        $response = $this->getApiClient()->httpGetResponse('/v1/spreadsheet/' . $dateString);

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

    public function verifyRefundSpreadsheet(UploadedFile $spreadsheetFile)
    {
        $result = $this->getApiClient()->httpPostFile('/v1/spreadsheet', $spreadsheetFile);

        return $result;
    }
}