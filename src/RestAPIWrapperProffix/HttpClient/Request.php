<?php

namespace Pitwch\RestAPIWrapperProffix\HttpClient;


/**
 * Class Request
 *
 * @package Pitwch\RestAPIWrapperProffix\HttpClient
 */
class Request
{

    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $method;
    /**
     * @var array
     */
    private $parameters;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var string
     */
    private $body;

    /**
     * Request constructor.
     *
     * @param string $url
     * @param string $method
     * @param array  $parameters
     * @param array  $headers
     * @param string $body
     */
    public function __construct($url = '', $method = 'GET', $parameters = [], $headers = [], $body = '')
    {
        $this->url        = $url;
        $this->method     = $method;
        $this->parameters = $parameters;
        $this->headers    = $headers;
        $this->body       = $body;
    }


    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }


    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }


    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }


    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }


    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
     * @return array
     */
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


    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
