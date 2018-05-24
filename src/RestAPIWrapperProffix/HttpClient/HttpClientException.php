<?php

namespace Pitwch\RestAPIWrapperProffix\HttpClient;

use Pitwch\RestAPIWrapperProffix\HttpClient\Request;
use Pitwch\RestAPIWrapperProffix\HttpClient\Response;


class HttpClientException extends \Exception
{

    private $request;

    private $response;


    public function __construct($message, $code, Request $request, Response $response)
    {
        parent::__construct($message, $code);

        $this->request  = $request;
        $this->response = $response;
    }


    public function getRequest()
    {
        return $this->request;
    }


    public function getResponse()
    {
        return $this->response;
    }
}
