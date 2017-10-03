<?php

namespace App\Action;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

/**
 * Class CsvDownloadAction
 * @package App\Action
 */
class CsvDownloadAction extends AbstractAction implements ApiClientInterface
{
    use ApiClientTrait;

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  TODO - If keeping this action then move logic below into a dedicated service
        $applications = $this->getApiClient()->httpGet('/dev/applications');

        $csvResource = fopen('php://output', 'w');
        foreach ($applications as $idx => $application) {
            $flattened = array_intersect_key($application, [
                'id' => 0,
                'applicant' => 0,
                'submitted' => 0,
                'expected' => 0
            ]);

            $flattened['donor.current.name.title'] = $application['donor']['current']['name']['title'];
            $flattened['donor.current.name.first'] = $application['donor']['current']['name']['first'];
            $flattened['donor.current.name.last'] = $application['donor']['current']['name']['last'];
            $flattened['donor.current.address.address-1'] = $application['donor']['current']['address']['address-1'];
            $flattened['donor.current.address.address-2'] = $application['donor']['current']['address']['address-2'];
            $flattened['donor.current.address.address-3'] = $application['donor']['current']['address']['address-3'];
            $flattened['donor.current.address.address-postcode'] = $application['donor']['current']['address']['address-postcode'];
            $flattened['donor.poa.name.title'] = '';
            $flattened['donor.poa.name.first'] = '';
            $flattened['donor.poa.name.last'] = '';
            if (array_key_exists('poa', $application['donor']) && array_key_exists('name', $application['donor']['poa'])) {
                $flattened['donor.poa.name.title'] = $application['donor']['poa']['name']['title'];
                $flattened['donor.poa.name.first'] = $application['donor']['poa']['name']['first'];
                $flattened['donor.poa.name.last'] = $application['donor']['poa']['name']['last'];
            }
            $flattened['donor.current.dob'] = $application['donor']['current']['dob'];

            $flattened['attorney.current.name.title'] = $application['attorney']['current']['name']['title'];
            $flattened['attorney.current.name.first'] = $application['attorney']['current']['name']['first'];
            $flattened['attorney.current.name.last'] = $application['attorney']['current']['name']['last'];
            $flattened['attorney.poa.name.title'] = '';
            $flattened['attorney.poa.name.first'] = '';
            $flattened['attorney.poa.name.last'] = '';
            if (array_key_exists('poa', $application['attorney']) && array_key_exists('name', $application['attorney']['poa'])) {
                $flattened['attorney.poa.name.title'] = $application['attorney']['poa']['name']['title'];
                $flattened['attorney.poa.name.first'] = $application['attorney']['poa']['name']['first'];
                $flattened['attorney.poa.name.last'] = $application['attorney']['poa']['name']['last'];
            }
            $flattened['attorney.current.dob'] = $application['attorney']['current']['dob'];

            $flattened['case-number.poa-case-number'] = array_key_exists('poa-case-number', $application['case-number']) ? $application['case-number']['poa-case-number'] : '';

            $flattened['contact.donor-postcode'] = '';
            $flattened['contact.attorney-postcode'] = '';
            if (array_key_exists('postcodes', $application)) {
                if (array_key_exists('donor-postcode', $application['postcodes'])) {
                    $flattened['contact.donor-postcode'] = $application['postcodes']['donor-postcode'];
                }
                if (array_key_exists('attorney-postcode', $application['postcodes'])) {
                    $flattened['contact.attorney-postcode'] = $application['postcodes']['attorney-postcode'];
                }
            }

            $flattened['contact.contact-options.email'] = 'no';
            $flattened['contact.email'] = '';
            if (array_key_exists('email', $application['contact'])) {
                $flattened['contact.email'] = $application['contact']['email'];
            }
            $flattened['contact.contact-options.phone'] = 'no';
            $flattened['contact.phone'] = '';
            if (array_key_exists('phone', $application['contact'])) {
                $flattened['contact.phone'] = $application['contact']['phone'];
            }

            $flattened['account.name'] = $application['account']['name'];
            $flattened['account.number'] = $application['account']['details']['account-number'];
            $flattened['account.sort-code'] = $application['account']['details']['sort-code'];

            if ($idx === 0) {
                fputcsv($csvResource, array_keys($flattened));
            }
            fputcsv($csvResource, $flattened);
        }

        $stream = new Stream($csvResource);
        $timestamp = time();
        $fileName = "Applications_{$timestamp}.csv";

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
