<?php

declare(strict_types=1);

namespace Saleh7\Zatca\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Saleh7\Zatca\Api\ComplianceCertificateResult;
use Saleh7\Zatca\Api\ProductionCertificateResult;
use Saleh7\Zatca\ZatcaAPI;
use Saleh7\Zatca\Exceptions\ZatcaApiException;

final class ZatcaAPITest extends TestCase
{
    /**
     * Helper method to set a custom Guzzle client in the ZatcaAPI instance.
     */
    private function setHttpClient(ZatcaAPI $api, Client $client): void
    {
        $ref = new ReflectionClass($api);
        $prop = $ref->getProperty('httpClient');
        $prop->setAccessible(true);
        $prop->setValue($api, $client);
    }

    /**
     * Test that an invalid environment throws an exception.
     */
    public function testInvalidEnvironment(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ZatcaAPI('invalid_env');
    }

    /**
     * Test loadCSRFromFile returns file content on success.
     */
    public function testLoadCSRFromFileSuccess(): void
    {
        $content = "MIIB3DCCAYMCAQAwVDEYMBYGA1UEAwwPTXkgT3JnYW5pemF0aW9uMRMwEQYDVQQKDApNeSBDb21wYW55MRYwFAYDVQQLDA1JVCBEZXBhcnRtZW50MQswCQYDVQQGEwJTQTBWMBAGByqGSM49AgEGBSuBBAAKA0IABHYPn3tSuNqgjJR/DEJsYtNkcylN0nzRwOUFE4fbm+r6ok72OTjdENwowSdqs6wngYLoo8kEAhQEQ7paSmlgGluggc8wgcwGCSqGSIb3DQEJDjGBvjCBuzAhBgkrBgEEAYI3FAIEFAwSWkFUQ0EtQ29kZS1TaWduaW5nMIGVBgNVHREEgY0wgYqkgYcwgYQxIDAeBgNVBAQMFzEtU2FsZWh8Mi0xbnwzLVNNRTAwMDIzMR8wHQYKCZImiZPyLGQBAQwPMzEyMzQ1Njc4OTAxMjMzMQ0wCwYDVQQMDAQxMTAwMRswGQYDVQQaDBJSaXlhZGggMTIzNCBTdHJlZXQxEzARBgNVBA8MClRlY2hub2xvZ3kwCgYIKoZIzj0EAwIDRwAwRAIgJMOBzaCGqhov7dKF/Ftb1smpMQvLURr8+xbbTaMWJtoCIA3Jz79S0UsaSob3n6zNZnm56aDCQ+20V6fbxKBz40dl";
        $tempFile = tempnam(sys_get_temp_dir(), 'csr_');
        file_put_contents($tempFile, $content);

        $api = new ZatcaAPI('sandbox');
        $result = $api->loadCSRFromFile($tempFile);
        $this->assertEquals($content, $result, 'CSR file content should match expected content.');

        unlink($tempFile);
    }

    /**
     * Test loadCSRFromFile throws an exception if the file does not exist.
     */
    public function testLoadCSRFromFileNotFound(): void
    {
        $api = new ZatcaAPI('sandbox');
        $this->expectException(Exception::class);
        $api->loadCSRFromFile('nonexistent_file.csr');
    }

    /**
     * Test that requestComplianceCertificate returns a valid result.
     */
    public function testRequestComplianceCertificateSuccess(): void
    {
        // Simulate a successful API response.
        $responseData = [
            'binarySecurityToken' => base64_encode("MIICBDCCAaqgAwIBAgIGAZT/XebzMAoGCCqGSM49BAMCMBUxEzARBgNVBAMMCmVJbnZvaWNpbmcwHhcNMjUwMjEzMTI1MjA2WhcNMzAwMjEyMjEwMDAwWjBUMRgwFgYDVQQDDA9NeSBPcmdhbml6YXRpb24xEzARBgNVBAoMCk15IENvbXBhbnkxFjAUBgNVBAsMDUlUIERlcGFydG1lbnQxCzAJBgNVBAYTAlNBMFYwEAYHKoZIzj0CAQYFK4EEAAoDQgAEdg+fe1K42qCMlH8MQmxi02RzKU3SfNHA5QUTh9ub6vqiTvY5ON0Q3CjBJ2qzrCeBguijyQQCFARDulpKaWAaW6OBqTCBpjAMBgNVHRMBAf8EAjAAMIGVBgNVHREEgY0wgYqkgYcwgYQxIDAeBgNVBAQMFzEtU2FsZWh8Mi0xbnwzLVNNRTAwMDIzMR8wHQYKCZImiZPyLGQBAQwPMzEyMzQ1Njc4OTAxMjMzMQ0wCwYDVQQMDAQxMTAwMRswGQYDVQQaDBJSaXlhZGggMTIzNCBTdHJlZXQxEzARBgNVBA8MClRlY2hub2xvZ3kwCgYIKoZIzj0EAwIDSAAwRQIgErxTxDItQktdppV5w8n4uChk0m9MAuCQAU7T/5TbGXcCIQDg/k1roWKR5lQF9IiNsca950t0BMefvBv3nEQodGegYQ=="),
            'secret'              => "Dehvg1fc8GF6Jwt5bOxXwC6enR93VxeNEo2mlUatfgw=",
            'requestID'           => "1234567890123"
        ];
        $mock = new MockHandler([
            new Response(200, [], json_encode($responseData))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $api = new ZatcaAPI('sandbox');
        $this->setHttpClient($api, $client);

        $csr = "MIIB3DCCAYMCAQAwVDEYMBYGA1UEAwwPTXkgT3JnYW5pemF0aW9uMRMwEQYDVQQKDApNeSBDb21wYW55MRYwFAYDVQQLDA1JVCBEZXBhcnRtZW50MQswCQYDVQQGEwJTQTBWMBAGByqGSM49AgEGBSuBBAAKA0IABHYPn3tSuNqgjJR/DEJsYtNkcylN0nzRwOUFE4fbm+r6ok72OTjdENwowSdqs6wngYLoo8kEAhQEQ7paSmlgGluggc8wgcwGCSqGSIb3DQEJDjGBvjCBuzAhBgkrBgEEAYI3FAIEFAwSWkFUQ0EtQ29kZS1TaWduaW5nMIGVBgNVHREEgY0wgYqkgYcwgYQxIDAeBgNVBAQMFzEtU2FsZWh8Mi0xbnwzLVNNRTAwMDIzMR8wHQYKCZImiZPyLGQBAQwPMzEyMzQ1Njc4OTAxMjMzMQ0wCwYDVQQMDAQxMTAwMRswGQYDVQQaDBJSaXlhZGggMTIzNCBTdHJlZXQxEzARBgNVBA8MClRlY2hub2xvZ3kwCgYIKoZIzj0EAwIDRwAwRAIgJMOBzaCGqhov7dKF/Ftb1smpMQvLURr8+xbbTaMWJtoCIA3Jz79S0UsaSob3n6zNZnm56aDCQ+20V6fbxKBz40dl";
        $result = $api->requestComplianceCertificate($csr, "123123");

        $this->assertInstanceOf(ComplianceCertificateResult::class, $result);
        $formattedCertificate = $result->getCertificate();
        $this->assertStringContainsString("-----BEGIN CERTIFICATE-----", $formattedCertificate);
        $this->assertStringContainsString("-----END CERTIFICATE-----", $formattedCertificate);
        $this->assertEquals("Dehvg1fc8GF6Jwt5bOxXwC6enR93VxeNEo2mlUatfgw=", $result->getSecret());
        $this->assertEquals("1234567890123", $result->getRequestId());
    }

    /**
     * Test that validateInvoiceCompliance returns the expected data.
     */
    public function testValidateInvoiceComplianceSuccess(): void
    {
        $responseData = [
            'invoiceHash' => 'lUatfgwehvg1fc8GF6Jwt5bOwCD6enR93VxeNEo2mlUatfgw=',
            'uuid'        => 'dummyUuid',
            'invoice'     => base64_encode("signedInvoiceData")
        ];
        $mock = new MockHandler([
            new Response(200, [], json_encode($responseData))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $api = new ZatcaAPI('sandbox');
        $this->setHttpClient($api, $client);

        $certificate = "dummy certificate";
        $secret = "dummySecret";
        $signedInvoice = "signedInvoiceData";
        $invoiceHash = "dummyHash";
        $uuid = "dummyUuid";

        $result = $api->validateInvoiceCompliance($certificate, $secret, $signedInvoice, $invoiceHash, $uuid);
        $this->assertIsArray($result);
        $this->assertEquals($responseData['invoiceHash'], $result['invoiceHash']);
    }

    /**
     * Test that requestProductionCertificate returns a valid production result.
     */
    public function testRequestProductionCertificateSuccess(): void
    {
        $responseData = [
            'binarySecurityToken' => base64_encode("dummyProductionCertificate"),
            'secret'              => "prodSecret",
            'requestID'           => "prodRequestID"
        ];
        $mock = new MockHandler([
            new Response(200, [], json_encode($responseData))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $api = new ZatcaAPI('sandbox');
        $this->setHttpClient($api, $client);

        $certificate = "dummy certificate";
        $secret = "dummySecret";
        $complianceRequestId = "dummyComplianceRequestID";

        $result = $api->requestProductionCertificate($certificate, $secret, $complianceRequestId);
        $this->assertInstanceOf(ProductionCertificateResult::class, $result);
        $this->assertStringContainsString("-----BEGIN CERTIFICATE-----", $result->getCertificate());
        $this->assertEquals("prodSecret", $result->getSecret());
        $this->assertEquals("prodRequestID", $result->getRequestId());
    }

    /**
     * Test that submitClearanceInvoice returns expected API data.
     */
    public function testSubmitClearanceInvoiceSuccess(): void
    {
        $responseData = [
            'invoiceHash' => 'dummyHash',
            'uuid'        => 'dummyUuid',
            'invoice'     => base64_encode("signedInvoiceData")
        ];
        $mock = new MockHandler([
            new Response(200, [], json_encode($responseData))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $api = new ZatcaAPI('sandbox');
        $this->setHttpClient($api, $client);

        $certificate = "dummy certificate";
        $secret = "dummySecret";
        $signedInvoice = "signedInvoiceData";
        $invoiceHash = "dummyHash";
        $egsUuid = "dummyUuid";

        $result = $api->submitClearanceInvoice($certificate, $secret, $signedInvoice, $invoiceHash, $egsUuid);
        $this->assertIsArray($result);
        $this->assertEquals($responseData['invoiceHash'], $result['invoiceHash']);
    }

    /**
     * Test that API error responses throw a ZatcaApiException.
     */
    public function testSendRequestFailure(): void
    {
        $errorResponseData = ['error' => 'Bad Request'];
        $mock = new MockHandler([
            new Response(400, [], json_encode($errorResponseData))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $api = new ZatcaAPI('sandbox');
        $this->setHttpClient($api, $client);

        $this->expectException(ZatcaApiException::class);
        $api->requestComplianceCertificate("dummy csr", "123123");
    }
    
}
