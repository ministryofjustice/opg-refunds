<?php

namespace Api\Service;

use Api\Exception;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 * @package Api\Service
 */
class Client
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $apiBaseUri;

    /**
     * @var string
     */
    private $authToken;

    /**
     * Client constructor
     *
     * @param HttpClient $httpClient
     * @param string $apiBaseUri
     * @param string|null $authToken
     */
    public function __construct(HttpClient $httpClient, string $apiBaseUri, string $authToken = null)
    {
        $this->httpClient = $httpClient;
        $this->apiBaseUri = $apiBaseUri;
        $this->authToken = $authToken;
    }

    /**
     * Send the authenticate API call
     *
     * @param string $email
     * @param string $password
     * @return array
     */
    public function authenticate(string $email, string $password)
    {
        return $this->httpPost('/v1/auth', [
            'email'    => $email,
            'password' => $password,
        ]);
    }

    /**
     * Get user details
     *
     * @param int $userId
     * @return array
     */
    public function getUser(int $userId)
    {
        return $this->httpGet('/v1/cases/user/' . $userId);
    }

    /**
     * Get all claims
     *
     * @return array
     */
    public function getClaims()
    {
        return $this->httpGet('/v1/cases/claim');
    }

    /**
     * Get SSCL spreadsheet containing all refundable claims
     *
     * @return ResponseInterface
     */
    public function getSpreadsheetResponse()
    {
        //TODO: Add date or other unique identifier allowing for previous spreadsheets to be downloaded

        //  Not using httpGet because the response of this API endpoint is binary, specifically a .xls file
        $url = new Uri($this->apiBaseUri . '/v1/cases/spreadsheet');

        $request = new Request('GET', $url, $this->buildHeaders());

        //  Can throw RuntimeException if there is a problem
        $response = $this->httpClient->sendRequest($request);

        return $response;
    }

    /**
     * Get all applications
     *
     * @return array
     */
    public function getApplications()
    {
        return $this->httpGet('/dev/applications');
    }

    /**
     * Performs a GET against the API
     *
     * @param string $path
     * @param array  $query
     * @return array|null
     * @throw RuntimeException | Exception\ApiException
     */
    public function httpGet($path, array $query = [])
    {
        $url = new Uri($this->apiBaseUri . $path);

        foreach ($query as $name => $value) {
            $url = Uri::withQueryValue($url, $name, $value);
        }

        $request = new Request('GET', $url, $this->buildHeaders());

        //  Can throw RuntimeException if there is a problem
        $response = $this->httpClient->sendRequest($request);

        switch ($response->getStatusCode()) {
            case 200:
                return $this->handleResponse($response);
            case 404:
                return null;
            default:
                return $this->handleErrorResponse($response);
        }
    }

    /**
     * Performs a POST against the API
     *
     * @param string $path
     * @param array  $payload
     * @return array
     * @throw RuntimeException | Exception\ApiException
     */
    public function httpPost($path, array $payload)
    {
        $url = new Uri($this->apiBaseUri . $path);

        $request = new Request('POST', $url, $this->buildHeaders(), json_encode($payload));

        $response = $this->httpClient->sendRequest($request);

        switch ($response->getStatusCode()) {
            case 200:
            case 201:
                return $this->handleResponse($response);
            default:
                return $this->handleErrorResponse($response);
        }
    }

    //  TODO - Create httpPatch function

    //  TODO - Create httpDelete function

    /**
     * Generates the standard set of HTTP headers expected by the API
     *
     * @return array
     */
    private function buildHeaders()
    {
        $headerLines = [
            'Accept'        => 'application/json',
            'Content-type'  => 'application/json',
        ];

        //  If the logged in user has an auth token already then set that in the header
        if (isset($this->authToken)) {
            $headerLines['token'] = $this->authToken;
        }

        return $headerLines;
    }

    /**
     * Successful response processing
     *
     * @param ResponseInterface $response
     * @return array
     * @throw Exception\ApiException
     */
    private function handleResponse(ResponseInterface $response)
    {
        $body = json_decode($response->getBody(), true);

        //  If the body isn't an array now then it wasn't JSON before
        if (!is_array($body)) {
            throw new Exception\ApiException('Malformed JSON response from server', $response->getStatusCode(), $response);
        }

        return $body;
    }

    /**
     * Unsuccessful response processing
     *
     * @param ResponseInterface $response
     * @return null
     * @throw Exception\ApiException
     */
    protected function handleErrorResponse(ResponseInterface $response)
    {
        $body = json_decode($response->getBody(), true);

        $message = 'HTTP:' . $response->getStatusCode() . ' - ';
        $message .= (is_array($body) ? print_r($body, true) : 'Unexpected response from server');

        throw new Exception\ApiException($message, $response->getStatusCode(), $response);
    }
}
