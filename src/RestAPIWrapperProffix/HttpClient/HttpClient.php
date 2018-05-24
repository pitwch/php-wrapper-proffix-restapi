<?php

namespace Pitwch\RestAPIWrapperProffix\HttpClient;


use Pitwch\RestAPIWrapperProffix\Client;
use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException;
use Pitwch\RestAPIWrapperProffix\HttpClient\Options;
use Pitwch\RestAPIWrapperProffix\HttpClient\Request;
use Pitwch\RestAPIWrapperProffix\HttpClient\Response;



class HttpClient
{
    protected $ch;
    protected $url;
    protected $apiDatabase;
    protected $apiModules;
    protected $apiUser;
    protected $apiPassword;
    protected $options;
    public $request;
    public $response;
    private $responseHeaders;
    protected $pxSessionId;

    /**
     * HttpClient constructor.
     * @param $url
     * @param $apiDatabase
     * @param $apiUser
     * @param $apiPassword
     * @param $apiModules
     * @param $options
     * @throws HttpClientException
     */
    public function __construct($url, $apiDatabase, $apiUser, $apiPassword, $apiModules, $options)
    {
        if (!\function_exists('curl_version')) {
            throw new HttpClientException('cURL is not installed on this server', -1, new Request(), new Response());
        }

        $this->options = new Options($options);
        $this->url = $this->buildApiUrl($url);
        $this->apiUser = $apiUser;
        $this->apiPassword = $apiPassword;
        $this->apiDatabase = $apiDatabase;
        $this->apiModules = $apiModules;
        //$this->options->doLogin() ? $this->pxSessionId = $this->login() : $this->pxSessionId = '';
        $this->pxSessionId = '';
    }


    /**
     * @return bool
     */
    protected function isSsl()
    {
        return 'https://' === \substr($this->url, 0, 8);
    }


    /**
     * @param $url
     * @return string
     */
    protected function buildApiUrl($url)
    {

        return \rtrim($url, '/') . $this->options->apiPrefix() . $this->options->getVersion() . '/';
    }


    /**
     * @param $url
     * @param array $parameters
     * @return string
     */
    protected function buildUrlQuery($url, $parameters = [])
    {

        if (!empty($parameters)) {

            if (empty($parameters['key'])) {
                $parameters['key'] = $this->options->getApiKey();
            }

            $url .= '?' . \http_build_query($parameters);
        }

        return $url;
    }

    /**
     * @return array
     */
    protected function buildLoginJson()
    {
        $loginJson = Array("Benutzer" => $this->apiUser,
            "Passwort" => $this->apiPassword,
            "Datenbank" => Array("Name" => $this->apiDatabase),
            "Module" => explode(",", $this->apiModules)
        );

        return $loginJson;
    }

    /**
     * @return string
     */
    protected function buildLoginUrl()
    {

        return \rtrim($this->url, '/') . '/' . $this->options->getLoginEndpoint();
    }

    /**
     * @return mixed|string
     */
    protected function login()
    {
        $headers = [];

        if($this->options->doLogin()){
            $headerarray = array("Cache-Control: no-cache","Content-Type: application/json","PxSessionId:" . $this->pxSessionId);
        } else{
            $headerarray =  array("Cache-Control: no-cache","Content-Type: application/json");
        }


        $body = \json_encode($this->buildLoginJson());
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->buildLoginUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->options->getTimeout(),
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headerarray,
        ));

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $lines = \explode("\n", $response);
            $lines = \array_filter($lines, 'trim');

            foreach ($lines as $index => $line) {
                // Remove HTTP/xxx params.
                if (strpos($line, ': ') === false) {
                    continue;
                }

                list($key, $value) = \explode(': ', $line);

                $headers[$key] = isset($headers[$key]) ? $headers[$key] . ', ' . trim($value) : trim($value);
            }
            return $headers['PxSessionId'];
        }


    }

    /**
     * @return bool|string
     */
    protected function logout()
    {


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->buildLoginUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->options->getTimeout(),
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: application/json",
                "PxSessionId:" . $this->pxSessionId,
            ),
        ));

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else return true;
    }

    /**
     * @param $method
     */
    protected function setupMethod($method)
    {
        if ('POST' == $method) {
            \curl_setopt($this->ch, CURLOPT_POST, true);
        } elseif (\in_array($method, ['PUT', 'DELETE', 'OPTIONS'])) {
            \curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * @param bool $sendData
     * @return array
     * @throws \Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException
     */
    protected function getRequestHeaders($sendData = false)
    {
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => $this->options->userAgent() . '/' . Client::VERSION,
            'PxSessionId' => $this->pxSessionId
        ];

        if ($sendData) {
            $headers['Content-Type'] = 'application/json;charset=utf-8';
        }

        return $headers;
    }

    /**
     * @param $endpoint
     * @param $method
     * @param array $data
     * @param array $parameters
     * @return mixed
     * @throws \Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException
     */
    protected function createRequest($endpoint, $method, $data = [], $parameters = [])
    {
        $body = '';
        $url = $this->url . $endpoint;
        $hasData = !empty($data);


        // Setup method.
        $this->setupMethod($method);


        // Include post fields.
        if ($hasData) {
            $body = \json_encode($data);
            \curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
        }

        $this->request = new Request(
            $this->buildUrlQuery($url, $parameters),
            $method,
            $parameters,
            $this->getRequestHeaders($hasData),
            $body
        );
        return $this->getRequest();
    }

    /**
     * @return array
     */
    protected function getResponseHeaders()
    {
        $headers = [];
        $lines = \explode("\n", $this->responseHeaders);
        $lines = \array_filter($lines, 'trim');

        foreach ($lines as $index => $line) {
            // Remove HTTP/xxx params.
            if (strpos($line, ': ') === false) {
                continue;
            }

            list($key, $value) = \explode(': ', $line);

            $headers[$key] = isset($headers[$key]) ? $headers[$key] . ', ' . trim($value) : trim($value);
        }

        return $headers;
    }

    /**
     * @return mixed
     * @throws \Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException
     */

    protected function createResponse()
    {

        // Set response headers.
        $this->responseHeaders = '';
        \curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, function ($_, $headers) {
            $this->responseHeaders .= $headers;
            return \strlen($headers);
        });

        // Get response data.
        $body = \curl_exec($this->ch);
        $code = \curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $headers = $this->getResponseHeaders();

        // Register response.
        $this->response = new Response($code, $headers, $body);

        return $this->getResponse();
    }

    /**
     *
     */
    protected function setDefaultCurlSettings()
    {
        $verifySsl = $this->options->verifySsl();
        $timeout = $this->options->getTimeout();
        $followRedirects = $this->options->getFollowRedirects();

        \curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
        if (!$verifySsl) {
            \curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $verifySsl);
        }
        if ($followRedirects) {
            \curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        }
        \curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        \curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
        \curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->request->getRawHeaders());
        \curl_setopt($this->ch, CURLOPT_URL, $this->request->getUrl());
    }

    /**
     * @param $parsedResponse
     * @throws HttpClientException
     */
    protected function lookForErrors($parsedResponse)
    {

        // Any non-200/201/202 response code indicates an error.
        if (!\in_array($this->response->getCode(), ['200', '201', '202'])) {
            $errors = isset($parsedResponse->errors) ? $parsedResponse->errors : $parsedResponse;

            if (is_array($errors)) {
                $errorMessage = $errors[0]->Message;
                $errorCode = $this->response->getCode();
            } else {
                $errorMessage = $errors->Message;
                $errorCode = $this->response->getCode();
            }

            throw new HttpClientException(
                \sprintf('Message: %s Code: %s', $errorMessage, $errorCode),
                $this->response->getCode(),
                $this->request,
                $this->response
            );

        }
    }

    /**
     * @return mixed
     * @throws \Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException
     */
    protected function processResponse()
    {
        $body = $this->response->getBody();

        $parsedResponse = \json_decode($body);

        // Test if return a valid JSON.
        if (JSON_ERROR_NONE !== json_last_error() && $this->response->getCode() != 201) {
            $message = function_exists('json_last_error_msg') ? json_last_error_msg() : 'Invalid JSON returned';
            throw new HttpClientException($message, $this->response->getCode(), $this->request, $this->response);
        }

        $this->lookForErrors($parsedResponse);

        return $parsedResponse;
    }

    /**
     * @param $endpoint
     * @param $method
     * @param array $data
     * @param array $parameters
     * @return mixed
     * @throws HttpClientException
     */
    public function request($endpoint, $method, $data = [], $parameters = [])
    {
        // Initialize cURL.
        $this->ch = \curl_init();

        // Set request args.
        $request = $this->createRequest($endpoint, $method, $data, $parameters);

        // Default cURL settings.
        $this->setDefaultCurlSettings();

        // Get response.
        $response = $this->createResponse();

        //Logout
        $this->options->doLogin() ? $this->logout($this->pxSessionId) : '';
        // Check for cURL errors.
        if (\curl_errno($this->ch)) {
            throw new HttpClientException('cURL Error: ' . \curl_error($this->ch), 0, $request, $response);
        }

        \curl_close($this->ch);

        return $this->processResponse();
    }


    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

}
