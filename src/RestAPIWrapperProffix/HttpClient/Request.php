<?php

namespace Pitwch\RestAPIWrapperProffix\HttpClient;


class Request
{

    private $url;
    private $method;
    private $parameters;
    private $headers;
    private $body;

    public function __construct($url = '', $method = 'POST', $parameters = [], $headers = [], $body = '')
    {
        $this->url        = $url;
        $this->method     = $method;
        $this->parameters = $parameters;
        $this->headers    = $headers;
        $this->body       = $body;
    }


    public function setUrl($url)
    {
        $this->url = $url;
    }


    public function setMethod($method)
    {
        $this->method = $method;
    }


    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }


    public function setBody($body)
    {
        $this->body = $body;
    }


    public function getUrl()
    {
        return $this->url;
    }


    public function getMethod()
    {
        return $this->method;
    }

    public function getParameters()
    {
        return $this->parameters;
    }


    public function getHeaders()
    {
        return $this->headers;
    }


    public function getRawHeaders()
    {
        $headers = [];

        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        return $headers;
    }


    public function getBody()
    {
        return $this->body;
    }
}
