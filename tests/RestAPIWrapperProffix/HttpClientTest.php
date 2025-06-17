<?php

namespace Pitwch\RestAPIWrapperProffix\Tests;

use PHPUnit\Framework\TestCase;
use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClient;
use Pitwch\RestAPIWrapperProffix\HttpClient\Options;

class HttpClientTest extends TestCase
{
    private HttpClient $httpClient;
    private array $defaultOptions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->defaultOptions = [
            'url' => 'http://fake-api.proffix.com',
            'apiDatabase' => 'FAKEDB',
            'apiUser' => 'testuser',
            'apiPassword' => 'password',
            'apiModules' => ['CRM', 'ADR'],
            'options' => [] // Default HttpClient Options
        ];

        $this->httpClient = new HttpClient(
            $this->defaultOptions['url'],
            $this->defaultOptions['apiDatabase'],
            $this->defaultOptions['apiUser'],
            $this->defaultOptions['apiPassword'],
            $this->defaultOptions['apiModules'],
            $this->defaultOptions['options']
        );
    }

    public function testHttpClientCanBeInstantiated(): void
    {
        $this->assertInstanceOf(HttpClient::class, $this->httpClient);
    }

    // More tests will be added here, e.g., for login, request methods, error handling.
    // These will likely require mocking cURL functions or HTTP responses.

}
