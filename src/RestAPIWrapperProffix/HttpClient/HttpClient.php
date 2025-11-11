<?php

namespace Pitwch\RestAPIWrapperProffix\HttpClient;

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
    protected $url;
    protected $apiDatabase;
    protected $apiModules;
    protected $apiUser;
    protected $apiPassword;
    protected $options;
    public $request;
    public $response;
    protected $pxSessionId;
    private $ch;
    private $responseHeaders = [];

    public function __construct($url, $apiDatabase, $apiUser, $apiPassword, $apiModules, $options = [])
    {
        if (!function_exists('curl_version')) {
            throw new HttpClientException('cURL is not installed on this server', -1, new Request(), new Response());
        }

        if (!is_array($options)) {
            $options = [];
        }

        $this->options = new Options($options);
        $this->url = $this->buildApiUrl($url);
        $this->apiUser = $apiUser;
        $this->apiPassword = $apiPassword;
        $this->apiDatabase = $apiDatabase;
        $this->apiModules = $apiModules;

        // Initialize session cache if enabled
        if ($this->options->isSessionCachingEnabled()) {
            $sessionCache = new SessionCache($apiUser, $apiDatabase, $url);
            $this->options->setSessionCache($sessionCache);
        }
    }

    protected function buildApiUrl($url)
    {
        return rtrim($url, '/') . $this->options->apiPrefix() . $this->options->getVersion() . '/';
    }

    protected function buildUrlQuery($url, $parameters = [])
    {
        if (!empty($parameters)) {
            if (array_key_exists('key', $parameters) && empty($parameters['key'])) {
                $parameters['key'] = $this->options->getApiKey();
            }
            $url .= '?' . http_build_query($parameters);
        }
        return $url;
    }

    protected function login()
    {
        $loginCredentials = [
            'Benutzer' => $this->apiUser,
            'Passwort' => $this->apiPassword,
            'Datenbank' => ['Name' => $this->apiDatabase],
            'Module' => is_array($this->apiModules) ? $this->apiModules : explode(',', $this->apiModules)
        ];

        $loginUrl = rtrim($this->url, '/') . '/' . $this->options->getLoginEndpoint();
        $body = json_encode($loginCredentials);

        curl_setopt($this->ch, CURLOPT_URL, $loginUrl);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Content-Length: ' . strlen($body)
        ]);

        $response = curl_exec($this->ch);
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $header = substr($response, 0, $headerSize);

        if (curl_errno($this->ch)) {
            throw new HttpClientException('cURL error during login: ' . curl_error($this->ch), curl_errno($this->ch), $this->request, null);
        }

        $this->pxSessionId = $this->extractSessionId($header);

        if (empty($this->pxSessionId)) {
            $responseBody = substr($response, $headerSize);
            $parsedBody = json_decode($responseBody);
            $errorMessage = $parsedBody->Message ?? 'Login failed: PxSessionId not found in response headers.';
            throw new HttpClientException($errorMessage, $httpCode, $this->request, null);
        }

        // Save session to cache if enabled
        if ($this->options->isSessionCachingEnabled()) {
            $sessionCache = $this->options->getSessionCache();
            if ($sessionCache !== null) {
                $sessionCache->save($this->pxSessionId);
            }
        }
    }

    protected function logout()
    {
        if (empty($this->pxSessionId)) {
            return;
        }

        $ch = curl_init();
        $logoutUrl = rtrim($this->url, '/') . '/' . $this->options->getLoginEndpoint();
        $headers = $this->getRequestHeaders(false);
        $rawHeaders = [];
        foreach ($headers as $key => $value) {
            $rawHeaders[] = $key . ': ' . $value;
        }

        curl_setopt($ch, CURLOPT_URL, $logoutUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $rawHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Use a short timeout

        curl_exec($ch);
        curl_close($ch);

        $this->pxSessionId = null;

        // Clear cached session if enabled
        if ($this->options->isSessionCachingEnabled()) {
            $sessionCache = $this->options->getSessionCache();
            if ($sessionCache !== null) {
                $sessionCache->clear();
            }
        }
    }



    protected function getRequestHeaders($sendData = false, $body = '')
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
            $headers['Content-Length'] = strlen($body);
        }
        return $headers;
    }

    protected function createRequest($endpoint, $method, $data = [], $parameters = [])
    {
        $url = $this->url . $endpoint;
        $body = json_encode($data);
        $this->request = new Request(
            $this->buildUrlQuery($url, $parameters),
            $method,
            $parameters,
            $this->getRequestHeaders(!empty($data), $body),
            $body
        );
    }

    protected function getResponseHeaders()
    {
        $headers = [];
        $lines = explode("\n", trim((string)$this->responseHeaders));
        foreach ($lines as $line) {
            if (strpos($line, ': ') !== false) {
                list($key, $value) = explode(': ', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        return $headers;
    }

    protected function createResponse()
    {
        $this->response = new Response();
    }

    protected function setDefaultCurlSettings()
    {
        $verifySsl = $this->options->verifySsl();
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
        if (!$verifySsl) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $verifySsl);
        }
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->options->getTimeout());
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_URL, $this->request->getUrl());
    }

    protected function lookForErrors($parsedResponse)
    {
        if (!in_array($this->response->getCode(), [200, 201, 202, 204])) {
            // Clear cached session on 401 Unauthorized
            if ($this->response->getCode() === 401) {
                $this->pxSessionId = null;
                if ($this->options->isSessionCachingEnabled()) {
                    $sessionCache = $this->options->getSessionCache();
                    if ($sessionCache !== null) {
                        $sessionCache->clear();
                    }
                }
            }
            
            $errorMessage = 'An unknown error occurred';
            if (isset($parsedResponse->Message)) {
                $errorMessage = $parsedResponse->Message;
            }
            throw new HttpClientException($errorMessage, $this->response->getCode(), $this->request, $this->response);
        }
    }

    protected function processResponse()
    {
        $body = $this->response->getBody();
        $parsedResponse = json_decode($body);
        if (json_last_error() !== JSON_ERROR_NONE && !in_array($this->response->getCode(), [201, 204])) {
            throw new HttpClientException(json_last_error_msg(), $this->response->getCode(), $this->request, $this->response);
        }
        $this->lookForErrors($parsedResponse);
        return $parsedResponse;
    }

    public function request($endpoint, $method, $data = [], $parameters = [], $login = true)
    {
        $this->prepareRequest($endpoint, $method, $data, $parameters, $login);
        return $this->executeCurl(true);
    }

    public function rawRequest($endpoint, $method, $data = [], $parameters = []): Response
    {
        $this->prepareRequest($endpoint, $method, $data, $parameters, true);
        return $this->executeCurl(false);
    }

    private function prepareRequest($endpoint, $method, $data, $parameters, $login)
    {
        $this->initCurl();
        $body = json_encode($data);
        $hasData = ($body !== '[]' && $body !== '{}') || !in_array($method, ['GET', 'DELETE']);

        $this->createRequest($endpoint, $method, $data, $parameters);

        if ($login && empty($this->pxSessionId)) {
            // Try to load cached session first
            if ($this->options->isSessionCachingEnabled()) {
                $sessionCache = $this->options->getSessionCache();
                if ($sessionCache !== null) {
                    $cachedSession = $sessionCache->load();
                    if ($cachedSession !== null && !empty($cachedSession)) {
                        $this->pxSessionId = $cachedSession;
                    }
                }
            }

            // If no cached session, perform login
            if (empty($this->pxSessionId)) {
                $this->login();
            }
        }

        // Always set the default settings for the actual request, AFTER a potential login
        $this->setDefaultCurlSettings();

        // Reset all relevant cURL options to a clean state before every request
        curl_setopt($this->ch, CURLOPT_HTTPGET, false);
        curl_setopt($this->ch, CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, null);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, null);

        // Explicitly configure the handle for the current request
        switch ($method) {
            case 'POST':
                curl_setopt($this->ch, CURLOPT_POST, true);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
                break;
            case 'GET':
                curl_setopt($this->ch, CURLOPT_HTTPGET, true);
                break;
            case 'PUT':
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
                break;
            case 'DELETE':
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }

        $rawHeaders = [];
        foreach ($this->getRequestHeaders($hasData, $body) as $key => $value) {
            $rawHeaders[] = $key . ': ' . $value;
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $rawHeaders);
    }

    private function executeCurl(bool $processJson = true)
    {
        $this->responseHeaders = '';
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) {
            $this->responseHeaders .= $header;
            return strlen($header);
        });

        $this->createResponse();
        $body = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            $error_msg = curl_error($this->ch);
            $error_no = curl_errno($this->ch);
            curl_close($this->ch);
            $this->ch = null;
            throw new HttpClientException('cURL error: ' . $error_msg, $error_no, $this->request, $this->response);
        }

        $this->response->setBody($body);
        $this->response->setCode(curl_getinfo($this->ch, CURLINFO_HTTP_CODE));
        $this->response->setHeaders($this->getResponseHeaders());

        curl_close($this->ch);
        $this->ch = null;

        if ($processJson) {
            return $this->processResponse();
        }

        $this->lookForErrors(null);
        return $this->response;
    }

    public function getRequest() { return $this->request; }
    public function getResponse() { return $this->response; }

    private function initCurl()
    {
        // Always create a new cURL handle to ensure a stateless request
        $this->ch = curl_init();
    }

    private function extractSessionId($headerBlock)
    {
        $lines = explode("\n", $headerBlock);
        foreach ($lines as $line) {
            if (stripos($line, 'PxSessionId:') === 0) {
                list(, $value) = explode(':', $line, 2);
                return trim($value);
            }
        }
        return null;
    }

    public function __destruct()
    {
        // Attempt to log out if a session was active.
        // The logout method is now self-contained and will handle its own cURL session.
        if (!empty($this->pxSessionId)) {
            $this->logout();
        }
    }
}
