<?php


namespace Pitwch\RestAPIWrapperProffix\HttpClient;


class Response
{

    private $code;
    private $headers;
    private $body;


    public function __construct($code = 0, $headers = [], $body = '')
    {
        $this->code    = $code;
        $this->headers = $headers;
        $this->body    = $body;
    }


    public function setCode($code)
    {
        $this->code = (int) $code;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }


    public function setBody($body)
    {
        $this->body = $body;
    }


    public function getCode()
    {
        return $this->code;
    }


    public function getHeaders()
    {
        return $this->headers;
    }

    public function getBody()
    {
        return $this->body;
    }
}
