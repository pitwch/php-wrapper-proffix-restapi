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

    const VERSION = '1.3';


    /**
     * @var HttpClient
     */
    protected $http;

    /**
     * Client constructor.
     *
     * @param string $url         The Proffix API URL
     * @param string $apiDatabase The Proffix Database
     * @param string $apiUser     The Proffix User
     * @param string $apiPassword The Proffix Password
     * @param array  $apiModules  The required Proffix Modules
     * @param array  $options     Additional options
     *
     * @throws HttpClient\HttpClientException
     */
    public function __construct($url, $apiDatabase, $apiUser, $apiPassword, $apiModules, $options = [])
    {
        $this->http = new HttpClient($url, $apiDatabase, $apiUser, $apiPassword, $apiModules, $options);
    }


    /**
     * @param string $endpoint
     * @param array  $data
     *
     * @return mixed
     *
     * @throws HttpClient\HttpClientException
     */
    public function post($endpoint, $data)
    {
        return $this->http->request($endpoint, 'POST', $data);
    }

    /**
     * @param string $endpoint
     * @param array  $data
     *
     * @return mixed
     *
     * @throws HttpClient\HttpClientException
     */
    public function put($endpoint, $data)
    {
        return $this->http->request($endpoint, 'PUT', $data);
    }

    /**
     * @param string $endpoint
     * @param array  $parameters
     *
     * @return mixed
     *
     * @throws HttpClient\HttpClientException
     */
    public function get($endpoint, $parameters = [])
    {

        return $this->http->request($endpoint, 'GET', [], $parameters);
    }

    /**
     * @param string $endpoint
     * @param array  $parameters
     *
     * @return mixed
     *
     * @throws HttpClient\HttpClientException
     */
    public function delete($endpoint, $parameters = [])
    {
        return $this->http->request($endpoint, 'DELETE', [], $parameters);
    }

    /**
     * @param string $px_api_key
     *
     * @return mixed
     *
     * @throws HttpClient\HttpClientException
     */
    public function info($px_api_key = '')
    {
        return $this->http->request('PRO/Info', 'GET', [], ['key' => $px_api_key], false);
    }

    /**
     * @param string $px_api_key
     *
     * @return mixed
     *
     * @throws HttpClient\HttpClientException
     */
    public function database($px_api_key = '')
    {
        return $this->http->request('PRO/Datenbank', 'GET', [], ['key' => $px_api_key], false);
    }


}
