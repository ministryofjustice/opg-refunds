<?php

namespace Api\Service;

use Api\Exception;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\UploadedFile;

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
     * Get SSCL spreadsheet containing all refundable claims
     *
     * @return ResponseInterface
     */
    public function getSpreadsheetResponse()
    {
        //TODO: Add date or other unique identifier allowing for previous spreadsheets to be downloaded

        //  Not using httpGet because the response of this API endpoint is binary, specifically a .xls file
        $url = new Uri($this->apiBaseUri . '/v1/spreadsheet');

        $request = new Request('GET', $url, $this->buildHeaders());

        return $this->httpClient->sendRequest($request);
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
        $response = $this->httpGetResponse($path, $query);

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
     * Performs a GET against the API but does not process the response. Response is returned for caller to process
     *
     * @param string $path
     * @param array  $query
     * @return ResponseInterface
     * @throw RuntimeException
     */
    public function httpGetResponse($path, array $query = [])
    {
        $url = new Uri($this->apiBaseUri . $path);

        foreach ($query as $name => $value) {
            $url = Uri::withQueryValue($url, $name, $value);
        }

        $request = new Request('GET', $url, $this->buildHeaders());

        //  Can throw RuntimeException if there is a problem
        $response = $this->httpClient->sendRequest($request);

        return $response;
    }

    /**
     * Performs a POST against the API
     *
     * @param string $path
     * @param array  $payload
     * @return array
     * @throw RuntimeException | Exception\ApiException
     */
    public function httpPost($path, array $payload = [])
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

    /**
     * Performs a POST against the API
     *
     * @param string $path
     * @param UploadedFile $uploadedFile
     * @return array
     * @throw RuntimeException | Exception\ApiException
     */
    public function httpPostFile($path, UploadedFile $uploadedFile)
    {
        $url = new Uri($this->apiBaseUri . $path);

        $headers = $this->buildHeaders();
        $headers['Content-Type'] = $uploadedFile->getClientMediaType();

        $request = new Request('POST', $url, $headers, $uploadedFile->getStream());

        $response = $this->httpClient->sendRequest($request);

        switch ($response->getStatusCode()) {
            case 200:
            case 201:
                return $this->handleResponse($response);
            default:
                return $this->handleErrorResponse($response);
        }
    }

    /**
     * Performs a PUT against the API
     *
     * @param string $path
     * @param array  $payload
     * @return array
     * @throw RuntimeException | Exception\ApiException
     */
    public function httpPut($path, array $payload = [])
    {
        $url = new Uri($this->apiBaseUri . $path);

        $request = new Request('PUT', $url, $this->buildHeaders(), json_encode($payload));

        $response = $this->httpClient->sendRequest($request);

        switch ($response->getStatusCode()) {
            case 200:
            case 201:
                return $this->handleResponse($response);
            default:
                return $this->handleErrorResponse($response);
        }
    }

    /**
     * Performs a PATCH against the API
     *
     * @param string $path
     * @param array  $payload
     * @return array
     * @throw RuntimeException | Exception\ApiException
     */
    public function httpPatch($path, array $payload = [])
    {
        $url = new Uri($this->apiBaseUri . $path);

        $request = new Request('PATCH', $url, $this->buildHeaders(), json_encode($payload));

        $response = $this->httpClient->sendRequest($request);

        switch ($response->getStatusCode()) {
            case 200:
            case 201:
                return $this->handleResponse($response);
            default:
                return $this->handleErrorResponse($response);
        }
    }

    /**
     * Performs a DELETE against the API
     *
     * @param string $path
     * @return array
     * @throw RuntimeException | Exception\ApiException
     */
    public function httpDelete($path)
    {
        $url = new Uri($this->apiBaseUri . $path);

        $request = new Request('DELETE', $url, $this->buildHeaders());

        $response = $this->httpClient->sendRequest($request);

        switch ($response->getStatusCode()) {
            case 200:
            case 201:
                return $this->handleResponse($response);
            default:
                return $this->handleErrorResponse($response);
        }
    }

    /**
     * Generates the standard set of HTTP headers expected by the API
     *
     * @return array
     */
    private function buildHeaders()
    {
        $headerLines = [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
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
            throw new Exception\ApiException($response, 'Malformed JSON response from server');
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
        throw new Exception\ApiException($response);
    }
}
