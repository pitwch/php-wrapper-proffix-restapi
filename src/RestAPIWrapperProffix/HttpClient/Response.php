<?php


namespace Pitwch\RestAPIWrapperProffix\HttpClient;


/**
 * Class Response
 *
 * @package Pitwch\RestAPIWrapperProffix\HttpClient
 */
class Response
{

    /**
     * @var int
     */
    private $code;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var string
     */
    private $body;


    /**
     * Response constructor.
     *
     * @param int    $code
     * @param array  $headers
     * @param string $body
     */
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


    /**
     * Returns the HTTP status code.
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }


    /**
     * Returns the HTTP headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
