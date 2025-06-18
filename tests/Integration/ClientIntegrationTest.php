<?php

namespace Pitwch\RestAPIWrapperProffix\Tests\Integration;

use Pitwch\RestAPIWrapperProffix\Client;
use Pitwch\RestAPIWrapperProffix\HttpClient\HttpClientException;
use PHPUnit\Framework\TestCase;

class ClientIntegrationTest extends TestCase
{
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();



        $this->client = new Client(
            $_ENV['PROFFIX_API_URL'],
            $_ENV['PROFFIX_API_DATABASE'],
            $_ENV['PROFFIX_API_USER'],
            $_ENV['PROFFIX_API_PASSWORD'],
            $_ENV['PROFFIX_API_MODULES']
        );
    }

    public function testCanGetAddressList(): void
    {
        $addresses = $this->client->get('ADR/Adresse');
        $response = $this->client->getHttpClient()->getResponse();

        $this->assertEquals(200, $response->getCode());
        $this->assertIsArray($addresses);
        $this->assertNotEmpty($addresses, 'Address list should not be empty.');
        $this->assertObjectHasProperty('AdressNr', $addresses[0]);
    }

    public function testCanGetCountryDetailsCH(): void
    {
        $country = $this->client->get('PRO/Land/CH');
        $response = $this->client->getHttpClient()->getResponse();

        $this->assertEquals(200, $response->getCode());
        $this->assertIsObject($country);
        $this->assertEquals('CH', $country->LandNr);
        $this->assertEquals('Schweiz', $country->Bezeichnung);
    }

    public function testCanCreateAddress(): void
    {
        $addressData = [
            "Name" => "Testfirma AG " . time(),
            "Ort" => "Zürich",
            "PLZ" => "8000",
            "Land" => ["LandNr" => "CH"],
            "Strasse" => "Testweg 1"
        ];
        $addressId = null;

        try {
            // 1. Create the address
            $this->client->post('ADR/Adresse', $addressData);
            $createResponse = $this->client->getHttpClient()->getResponse();
            $this->assertEquals(201, $createResponse->getCode(), 'HTTP status code should be 201 Created');
            $headers = $createResponse->getHeaders();
            $this->assertArrayHasKey('Location', $headers, 'Response must contain Location header');
            preg_match('/(\d+)$/', $headers['Location'], $matches);
            $addressId = $matches[1];
            $this->assertNotEmpty($addressId, 'Could not extract new AdressNr from Location header');

            // 2. Verify the address was created by fetching it
            $newAddress = $this->client->get('ADR/Adresse/' . $addressId);
            $this->assertEquals($addressData['Name'], $newAddress->Name);
            $this->assertEquals($addressData['Ort'], $newAddress->Ort);

        } finally {
            // 3. Cleanup: Delete the created address
            if ($addressId) {
                try {
                    $this->client->delete('ADR/Adresse/' . $addressId);
                } catch (HttpClientException $e) {
                    fwrite(STDERR, "Cleanup failed for AdressNr {$addressId}: " . $e->getMessage() . "\n");
                }
            }
        }
    }

    public function testCanUpdateNewlyCreatedAddress(): void
    {
        $baseName = "Testfirma Update " . time();
        $initialAddressData = [
            "Name" => $baseName,
            "Ort" => "Updateville",
            "PLZ" => "8888",
            "Land" => ["LandNr" => "CH"],
            "Strasse" => "Testweg 1"
        ];
        $addressId = null;

        try {
            // 1. Create a new address
            $this->client->post('ADR/Adresse', $initialAddressData);
            $createResponse = $this->client->getHttpClient()->getResponse();
            $this->assertEquals(201, $createResponse->getCode());
            $headers = $createResponse->getHeaders();
            preg_match('/(\d+)$/', $headers['Location'], $matches);
            $addressId = $matches[1];
            sleep(3); // Give API time to process

            // 2. Update the address
            $updatedName = $baseName . " Updated";
                                    $updateData = array_merge($initialAddressData, ['Name' => $updatedName, 'AdressNr' => $addressId]);
            $this->client->put('ADR/Adresse/' . $addressId, $updateData);
            $updateResponse = $this->client->getHttpClient()->getResponse();
            $this->assertEquals(204, $updateResponse->getCode());

            // 3. Verify the update
            $updatedAddress = $this->client->get('ADR/Adresse/' . $addressId);
            $this->assertEquals($updatedName, $updatedAddress->Name);

        } finally {
            // 4. Cleanup
            if ($addressId) {
                $this->client->delete('ADR/Adresse/' . $addressId);
            }
        }
    }

    public function testCanDeleteAddress(): void
    {
        $addressData = [
            "Name" => "Firma zum Löschen " . time(),
            "Ort" => "Deleteburg",
            "PLZ" => "1234",
            "Land" => ["LandNr" => "CH"],
            "Strasse" => "Wegwerfweg 1"
        ];
        $addressId = null;

        // 1. Create a new address
        $this->client->post('ADR/Adresse', $addressData);
        $createResponse = $this->client->getHttpClient()->getResponse();
        $this->assertEquals(201, $createResponse->getCode());
        $headers = $createResponse->getHeaders();
        preg_match('/(\d+)$/', $headers['Location'], $matches);
        $addressId = $matches[1];

        // 2. Delete the address
        $this->client->delete('ADR/Adresse/' . $addressId);
        $deleteResponse = $this->client->getHttpClient()->getResponse();
        $this->assertEquals(204, $deleteResponse->getCode());

        // 3. Verify it's gone (soft delete).
        $getResponse = $this->client->get('ADR/Adresse/' . $addressId);
                $this->assertTrue($getResponse->Geloescht);
    }
}
