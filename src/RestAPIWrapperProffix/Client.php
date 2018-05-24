<?php


namespace Pitwch\RestAPIWrapperProffix;

use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClient;


class Client
{

    const VERSION = '1.0.2';


    public $http;

    public function __construct($url, $apiDatabase, $apiUser, $apiPassword, $apiModules, $options = [])
    {
        $this->http = new HttpClient($url, $apiDatabase, $apiUser, $apiPassword, $apiModules, $options);
    }


    public function post($endpoint, $data)
    {
        return $this->http->request($endpoint, 'POST', $data);
    }

    public function put($endpoint, $data)
    {
        return $this->http->request($endpoint, 'PUT', $data);
    }

    public function get($endpoint, $parameters = [])
    {

        return $this->http->request($endpoint, 'GET', [], $parameters);
    }

    public function delete($endpoint, $parameters = [])
    {
        return $this->http->request($endpoint, 'DELETE', [], $parameters);
    }

    public function options($endpoint)
    {
        return $this->http->request($endpoint, 'OPTIONS', [], []);

    }

    public function info($px_api_key = ''){

        return $this->http->request('PRO/Info', 'GET', [], array('key'=>$px_api_key));

    }

    public function database($px_api_key = ''){

        return $this->http->request('PRO/Datenbank', 'GET', [], array('key'=>$px_api_key));

    }


}
