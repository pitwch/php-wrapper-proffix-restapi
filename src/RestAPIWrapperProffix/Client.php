<?php

namespace Pitwch\RestAPIWrapperProffix;

use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClient;

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
}

