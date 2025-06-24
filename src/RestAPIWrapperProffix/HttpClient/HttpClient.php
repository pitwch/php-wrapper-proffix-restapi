<?php

namespace Pitwch\RestAPIWrapperProffix\HttpClient;


use Pitwch\RestAPIWrapperProffix\Client;
use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException;
use Pitwch\RestAPIWrapperProffix\HttpClient\Options;
use Pitwch\RestAPIWrapperProffix\HttpClient\Request;
use Pitwch\RestAPIWrapperProffix\HttpClient\Response;



/**
 * Class HttpClient
 *
 * @package Pitwch\RestAPIWrapperProffix\HttpClient
 */
class HttpClient
{
    /**
     * @var string The Proffix API URL
     */
    protected $url;

    /**
     * @var string The Proffix API Database
     */
    protected $apiDatabase;

    /**
     * @var array The Proffix API Modules
     */
    protected $apiModules;

    /**
     * @var string The Proffix API User
     */
    protected $apiUser;

    /**
     * @var string The Proffix API Password
     */
    protected $apiPassword;

    /**
     * @var Options The options for the client
     */
    protected $options;

    /**
     * @var Request The request object
     */
    public $request;

    /**
     * @var Response The response object
     */
    public $response;

    /**
     * @var string The Proffix Session ID
     */
    protected $pxSessionId;

    /**
     * @var resource|\CurlHandle The cURL handle
     */
    private $ch;

    /**
     * @var array The response headers
     */
    private $responseHeaders = [];

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
    /**
     * HttpClient constructor.
     *
     * @param string $url         The Proffix API URL
     * @param string $apiDatabase The Proffix Database
     * @param string $apiUser     The Proffix User
     * @param string $apiPassword The Proffix Password
     * @param array  $apiModules  The required Proffix Modules
     * @param array  $options     Additional options
     *
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

    }


    /**
     * Check if the connection is SSL
     *
     * @return bool
     */
    protected function isSsl()
    {
        return 'https://' === \substr($this->url, 0, 8);
    }


    /**
     * Build the API URL
     *
     * @param string $url
     *
     * @return string
     */
    protected function buildApiUrl($url)
    {

        return \rtrim($url, '/') . $this->options->apiPrefix() . $this->options->getVersion() . '/';
    }


    /**
     * Build the URL with query parameters
     *
     * @param string $url
     * @param array  $parameters
     *
     * @return string
     */
    protected function buildUrlQuery($url, $parameters = [])
    {
        //Create empty query
        $query = array();

        if (!empty($parameters)) {

            //Check if we need to send ?key Param
            if(array_key_exists('key',$parameters)){

                //Check if Param Key is empty -> use from Options
                if(empty($parameters['key'])){
                    $parameters['key'] = $this->options->getApiKey();

                }
            }

            $url .= '?'. \http_build_query($parameters);

        }

        return $url;
    }

    /**
     * Build the JSON for the login request
     *
     * @return array
     */
    protected function buildLoginJson()
    {
        $loginJson = [
            'Benutzer' => $this->apiUser,
            'Passwort' => $this->apiPassword,
            'Datenbank' => ['Name' => $this->apiDatabase],
            'Module' => is_array($this->apiModules) ? $this->apiModules : explode(',', $this->apiModules)
        ];

        return $loginJson;
    }

    /**
     * Build the login URL
     *
     * @return string
     */
    protected function buildLoginUrl()
    {

        return \rtrim($this->url, '/') . '/' . $this->options->getLoginEndpoint();
    }

    /**
     * Login to Proffix
     *
     * @return string The PxSessionId
     *
     * @throws HttpClientException
     */
    protected function login()
    {
        // IMPORTANT: login() now uses the cURL handle initialized by request().
        // It will temporarily set its own options on this handle.
        // Login has specific cURL needs, especially CURLOPT_HEADER = true.

        $body = \json_encode($this->buildLoginJson());
        $loginHeaders = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($body),
            'Accept: application/json' // Good practice to include Accept for login too
        ];

        \curl_setopt($this->ch, CURLOPT_URL, $this->buildLoginUrl());
        \curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        \curl_setopt($this->ch, CURLOPT_HTTPHEADER, $loginHeaders);
        \curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
        \curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true); // Make sure login also returns response
        \curl_setopt($this->ch, CURLOPT_HEADER, true); // Crucial for extracting PxSessionId from login response headers

        $response = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            throw new HttpClientException('cURL error: ' . curl_error($this->ch), curl_errno($this->ch), $this->request, $this->response);
        }

        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);

        $this->pxSessionId = $this->extractSessionId($header);
        
        if (empty($this->pxSessionId)) {
            $responseBody = substr($response, $headerSize);
            $parsedBody = json_decode($responseBody);
            $errorMessage = 'Failed to retrieve PxSessionId from login response.';
            if (isset($parsedBody->Message)) {
                $errorMessage .= ' Proffix API Error: ' . $parsedBody->Message;
            }
            throw new HttpClientException($errorMessage, 401, $this->request, $this->response);
        }

        return $this->pxSessionId;
    }

    /**
     * Logout from Proffix
     *
     * @return bool
     *
     * @throws HttpClientException
     */
    protected function logout()
    {
        if (empty($this->pxSessionId)) {
            return true;
        }

        $this->initCurl();

        $headers = [
            'PxSessionId: ' . $this->pxSessionId
        ];

        curl_setopt($this->ch, CURLOPT_URL, $this->buildLoginUrl());
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            throw new HttpClientException('cURL error on logout: ' . curl_error($this->ch), curl_errno($this->ch), $this->request, $this->response);
        }

        $this->pxSessionId = null;
        return true;
    }

    /**
     * Set the HTTP method for the cURL request
     *
     * @param string $method
     */
    protected function setupMethod($method)
    {
        // Reset method-specific options first to ensure a clean state
        \curl_setopt($this->ch, CURLOPT_HTTPGET, false);
        \curl_setopt($this->ch, CURLOPT_POST, false);
        \curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, null); // Reset custom request

        if ('GET' == $method) {
            \curl_setopt($this->ch, CURLOPT_HTTPGET, true);
        } elseif ('POST' == $method) {
            \curl_setopt($this->ch, CURLOPT_POST, true);
        } elseif (\in_array($method, ['PUT', 'DELETE', 'OPTIONS'])) {
            \curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        }
        // For POST, PUT, DELETE with body, CURLOPT_POSTFIELDS will be set later in request()
    }

    /**
     * Get the request headers
     *
     * @param bool $sendData
     *
     * @return array
     *
     * @throws HttpClientException
     */
    protected function getRequestHeaders($sendData = false)
    {
        $headers = [
            'Accept' => 'application/json',
                        'User-Agent' => $this->options->userAgent() . '/' . Options::VERSION,
        ];

        if (!empty($this->pxSessionId)) {
            $headers['PxSessionId'] = $this->pxSessionId;
        }

        if ($sendData) {
            $headers['Content-Type'] = 'application/json;charset=utf-8';
        }

        return $headers;
    }

    /**
     * Create the request object
     *
     * @param string $endpoint
     * @param string $method
     * @param array  $data
     * @param array  $parameters
     *
     * @return Request
     *
     * @throws HttpClientException
     */
    protected function createRequest($endpoint, $method, $data = [], $parameters = [])
    {
        $body = '';
        $url = $this->url . $endpoint;
        $hasData = !empty($data);

        if ($hasData) {
            $body = \json_encode($data);
        }

        $this->request = new Request(
            $this->buildUrlQuery($url, $parameters),
            $method,
            $parameters,
            $this->getRequestHeaders($hasData),
            $body
        );

        return $this->request;
    }

    /**
     * Get the response headers
     *
     * @return array
     */
    protected function getResponseHeaders()
    {
        $headers = [];
        $lines = explode("\n", (string)$this->responseHeaders);
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
     * Create the response object
     *
     * @return Response
     *
     * @throws HttpClientException
     */
    protected function createResponse()
    {
        $this->response = new Response();
    }

    /**
     * Set the default cURL settings
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
        \curl_setopt($this->ch, CURLOPT_HEADER, false); // Default for API calls: no headers in body
        // Note: CURLOPT_HTTPHEADER is NOT set here anymore. It's set in request() method.
        \curl_setopt($this->ch, CURLOPT_URL, $this->request->getUrl());
    }

    /**
     * Check for errors in the response
     *
     * @param mixed $parsedResponse
     *
     * @throws HttpClientException
     */
    protected function lookForErrors($parsedResponse)
    {
        // Any non-200/201/202/204 response code indicates an error.
        if (!in_array($this->response->getCode(), ['200', '201', '202', '204'])) {
            $errorMessage = 'An unknown error occurred';

            if (isset($parsedResponse->Message)) {
                $errorMessage = $parsedResponse->Message;
            } elseif (is_string($parsedResponse)) {
                $errorMessage = $parsedResponse;
            }

            if (isset($parsedResponse->Fields) && is_array($parsedResponse->Fields)) {
                $fieldErrors = [];
                foreach ($parsedResponse->Fields as $field) {
                    $fieldErrors[] = sprintf(
                        'Field: %s (%s) - %s',
                        $field->Name ?? 'N/A',
                        $field->Reason ?? 'N/A',
                        $field->Message ?? 'N/A'
                    );
                }
                if (!empty($fieldErrors)) {
                    $errorMessage .= ': ' . implode('; ', $fieldErrors);
                }
            }

            throw new HttpClientException(
                sprintf('Error: %s', $errorMessage),
                $this->response->getCode(),
                $this->request,
                $this->response
            );
        }
    }
    /**
     * @param $errors
     * @return array
     */
    protected function parsePxErrorMessage($errors)
    {
        foreach ($errors as $error){
            $clean[] = $error;
        }
    }
    /**
     * Process the response
     *
     * @return mixed
     *
     * @throws HttpClientException
     */
    protected function processResponse()
    {
        $body = $this->response->getBody();

        $parsedResponse = \json_decode($body);

        // Test if return a valid JSON.
        if (JSON_ERROR_NONE !== json_last_error() && ($this->response->getCode() != 201 && $this->response->getCode() != 204)) {
            $message = function_exists('json_last_error_msg') ? json_last_error_msg() : 'Invalid JSON returned';
            throw new HttpClientException($message, $this->response->getCode(), $this->request, $this->response);
        }

        $this->lookForErrors($parsedResponse);

        return $parsedResponse;
    }



    public function request($endpoint, $method, $data = [], $parameters = [], $login = true)
    {
        $this->prepareRequest($endpoint, $method, $data, $parameters, $login);
        return $this->executeCurl(true);
    }

    /**
     * @param $endpoint
     * @param $method
     * @param array $data
     * @param array $parameters
     * @return Response
     * @throws HttpClientException
     */
    public function rawRequest($endpoint, $method, $data = [], $parameters = []): Response
    {
        $this->prepareRequest($endpoint, $method, $data, $parameters, true); // Login is always required for raw requests
        return $this->executeCurl(false);
    }

    /**
     * @param $endpoint
     * @param $method
     * @param $data
     * @param $parameters
     * @param $login
     * @throws HttpClientException
     */
    private function prepareRequest($endpoint, $method, $data, $parameters, $login)
    {
        $this->initCurl();

        // Create the request object for the main operation.
        $this->createRequest($endpoint, $method, $data, $parameters);

        // If login is required, it's performed first.
        if ($login && empty($this->pxSessionId)) {
            $this->login();
        }

        // Apply default cURL settings for the MAIN request.
        $this->setDefaultCurlSettings();

        // Get the final headers for this specific request (which will include PxSessionId if login occurred)
        $finalRequestHeaders = $this->getRequestHeaders(!empty($data));
        $rawFinalRequestHeaders = [];
        foreach ($finalRequestHeaders as $key => $value) {
            $rawFinalRequestHeaders[] = $key . ': ' . $value;
        }
        \curl_setopt($this->ch, CURLOPT_HTTPHEADER, $rawFinalRequestHeaders);

        // Setup method.
        $this->setupMethod($method);

        // Include post fields.
        if (!empty($data)) {
            $body = \json_encode($data);
            \curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
        }

        $this->createResponse();
    }

    /**
     * @param bool $processJson
     * @return mixed|Response
     * @throws HttpClientException
     */
    private function executeCurl(bool $processJson = true)
    {
        // Set response headers callback
        $this->responseHeaders = '';
        \curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) {
            $this->responseHeaders .= $header;
            return strlen($header);
        });

        $body = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            throw new HttpClientException('cURL error: ' . curl_error($this->ch), curl_errno($this->ch), $this->request, $this->response);
        }

        $this->response->setBody($body);
        $this->response->setCode(curl_getinfo($this->ch, CURLINFO_HTTP_CODE));
        $this->response->setHeaders($this->getResponseHeaders());

        if ($processJson) {
            // processResponse will decode and also call lookForErrors
            return $this->processResponse();
        }

        // For raw requests, we only check for non-2xx status codes.
        if (!in_array($this->response->getCode(), ['200', '201', '202', '204'])) {
             // Try to parse the body as JSON to get a detailed error message
            $parsedError = \json_decode($this->response->getBody());
            if (JSON_ERROR_NONE === json_last_error()) {
                // It's a JSON error response, pass it to lookForErrors
                 $this->lookForErrors($parsedError);
            } else {
                // Not a JSON error, create a generic exception
                throw new HttpClientException(
                    'HTTP Error ' . $this->response->getCode(),
                    $this->response->getCode(),
                    $this->request,
                    $this->response
                );
            }
        }

        return $this->response;
    }

    /**
     * Get the request object
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Initialize the cURL handle
     */
    private function initCurl()
    {
        if (!$this->ch) {
            $this->ch = curl_init();
        }
    }

    /**
     * Extract the session ID from the response headers
     *
     * @param string $header
     *
     * @return string|null
     */
    private function extractSessionId($header)
    {
        foreach (explode("\r\n", $header) as $line) {
            if (strpos($line, 'PxSessionId:') === 0) {
                return trim(substr($line, strlen('PxSessionId:')));
            }
        }

        return null;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->ch) {
            if(!empty($this->pxSessionId)){
                $this->logout();
            }
            curl_close($this->ch);
        }
    }
}
