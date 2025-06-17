<?php

namespace Pitwch\RestAPIWrapperProffix\HttpClient;

use Pitwch\RestAPIWrapperProffix\HttpClient\Request;
use Pitwch\RestAPIWrapperProffix\HttpClient\Response;


/**
 * Class HttpClientException
 *
 * @package Pitwch\RestAPIWrapperProffix\HttpClient
 */
class HttpClientException extends \Exception
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;


    /**
     * HttpClientException constructor.
     *
     * @param string   $message
     * @param int      $code
     * @param Request  $request
     * @param Response $response
     */
    public function __construct($message, $code, Request $request, Response $response)
    {
        parent::__construct($message, $code);

        $this->request  = $request;
        $this->response = $response;
    }


    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
