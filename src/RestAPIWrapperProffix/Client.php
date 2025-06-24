<?php

namespace Pitwch\RestAPIWrapperProffix;

use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClient;
use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException;

/**
 * Class Client
 *
 * @package Pitwch\RestAPIWrapperProffix
 */
class Client
{
    protected $httpClient;

    public function __construct($url, $apiDatabase, $apiUser, $apiPassword, $apiModules, $options = [])
    {
        $this->httpClient = new HttpClient($url, $apiDatabase, $apiUser, $apiPassword, $apiModules, $options);
    }

    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function get($endpoint, $parameters = [])
    {
        return $this->httpClient->request($endpoint, 'GET', [], $parameters);
    }

    public function post($endpoint, $data = [])
    {
        return $this->httpClient->request($endpoint, 'POST', $data);
    }

    public function put($endpoint, $data = [])
    {
        return $this->httpClient->request($endpoint, 'PUT', $data);
    }

    public function delete($endpoint, $parameters = [])
    {
        return $this->httpClient->request($endpoint, 'DELETE', [], $parameters);
    }

    public function info($px_api_key = '')
    {
        return $this->httpClient->request('PRO/Info', 'GET', [], ['key' => $px_api_key], false);
    }

    public function database($px_api_key = '')
    {
        return $this->httpClient->request('PRO/Datenbank', 'GET', [], ['key' => $px_api_key], false);
    }

    /**
     * Generates a list and returns the file content.
     *
     * @param int   $listenr The ID of the list to generate.
     * @param array $body    The request body for generating the list.
     * @return array An array containing the response body, headers, and status code of the file download.
     * @throws HttpClientException
     */
    public function getList(int $listenr, array $body = []): \Pitwch\RestAPIWrapperProffix\HttpClient\Response
    {
        // First, send a POST request to generate the list file.
        // The `post` method automatically handles JSON decoding and error checking.
        $this->post('PRO/Liste/' . $listenr . '/generieren', $body);

        // After a successful request, the HttpClient holds the last response.
        $postResponse = $this->getHttpClient()->getResponse();

        // The API returns 201 Created on success, which is already validated by lookForErrors.
        // We just need to get the Location header.
        $postHeaders = $postResponse->getHeaders();
        if (!isset($postHeaders['Location'])) {
            throw new HttpClientException('Location header not found in response for list generation.', 404, $this->getHttpClient()->getRequest(), $postResponse);
        }

        // Extract the file ID from the Location header
        $dateiNr = $this->convertLocationToId($postHeaders['Location']);

        // Use the new `rawRequest` method to download the file.
        // This method returns a Response object directly, without trying to parse the body as JSON.
        return $this->httpClient->rawRequest('PRO/Datei/' . $dateiNr, 'GET');
    }

    /**
     * Extracts the file ID from the Location header URL.
     * e.g. /v4/PRO/Datei/12345 -> 12345
     *
     * @param string $location
     * @return string
     */
    private function convertLocationToId(string $location): string
    {
        return basename($location);
    }
}

