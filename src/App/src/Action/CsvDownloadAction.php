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
        $applications = $this->getApiClient()->getApplications();

        $csvResource = fopen('php://output', 'w');
        foreach ($applications as $idx => $application) {
            $flattened = array_intersect_key($application, [
                'id' => 0,
                'applicant' => 0,
                'submitted' => 0,
                'expected' => 0
            ]);

            $flattened['donor.name.title'] = $application['donor']['name']['title'];
            $flattened['donor.name.first'] = $application['donor']['name']['first'];
            $flattened['donor.name.last'] = $application['donor']['name']['last'];
            $flattened['donor.poa-name.title'] = '';
            $flattened['donor.poa-name.first'] = '';
            $flattened['donor.poa-name.last'] = '';
            if (array_key_exists('poa-name', $application['donor'])) {
                $flattened['donor.poa-name.title'] = $application['donor']['poa-name']['title'];
                $flattened['donor.poa-name.first'] = $application['donor']['poa-name']['first'];
                $flattened['donor.poa-name.last'] = $application['donor']['poa-name']['last'];
            }
            $flattened['donor.dob'] = $application['donor']['dob'];

            $flattened['attorney.title'] = $application['attorney']['name']['title'];
            $flattened['attorney.first-name'] = $application['attorney']['name']['first'];
            $flattened['attorney.last-name'] = $application['attorney']['name']['last'];
            $flattened['attorney.poa-name.title'] = '';
            $flattened['attorney.poa-name.first'] = '';
            $flattened['attorney.poa-name.last'] = '';
            if (array_key_exists('poa-name', $application['attorney'])) {
                $flattened['attorney.poa-name.title'] = $application['attorney']['poa-name']['title'];
                $flattened['attorney.poa-name.first'] = $application['attorney']['poa-name']['first'];
                $flattened['attorney.poa-name.last'] = $application['attorney']['poa-name']['last'];
            }
            $flattened['attorney.dob'] = $application['attorney']['dob'];

            $flattened['case-number.have-poa-case-number'] = $application['case-number']['have-poa-case-number'];
            $flattened['case-number.poa-case-number'] = $flattened['case-number.have-poa-case-number'] === 'yes' ? $application['case-number']['poa-case-number'] : '';

            $flattened['postcodes.postcode-options.donor-postcode'] = 'no';
            $flattened['contact.donor-postcode'] = '';
            $flattened['postcodes.postcode-options.attorney-postcode'] = 'no';
            $flattened['contact.attorney-postcode'] = '';
            if (array_key_exists('postcodes', $application)) {
                if (in_array('donor-postcode', $application['postcodes']['postcode-options'])) {
                    $flattened['postcodes.postcode-options.donor-postcode'] = 'yes';
                    $flattened['contact.donor-postcode'] = $application['postcodes']['donor-postcode'];
                }
                if (in_array('attorney-postcode', $application['postcodes']['postcode-options'])) {
                    $flattened['postcodes.postcode-options.attorney-postcode'] = 'yes';
                    $flattened['contact.attorney-postcode'] = $application['postcodes']['attorney-postcode'];
                }
            }

            $flattened['contact.contact-options.email'] = 'no';
            $flattened['contact.email'] = '';
            if (in_array('email', $application['contact']['contact-options'])) {
                $flattened['contact.contact-options.email'] = 'yes';
                $flattened['contact.email'] = $application['contact']['email'];
            }
            $flattened['contact.contact-options.phone'] = 'no';
            $flattened['contact.phone'] = '';
            if (in_array('phone', $application['contact']['contact-options'])) {
                $flattened['contact.contact-options.phone'] = 'yes';
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
