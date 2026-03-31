<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;
use Saleh7\Zatca\ZatcaAPI;
use Saleh7\Zatca\Exceptions\ZatcaApiException;
use Saleh7\Zatca\Api\ComplianceCertificateResult;
use Saleh7\Zatca\Api\ProductionCertificateResult;
use Saleh7\Zatca\Api\ReportingResponse;
use Saleh7\Zatca\Api\ClearanceResponse;
use Saleh7\Zatca\Api\ComplianceInvoiceResponse;

final class ZatcaAPITest extends TestCase
{
    /**
     * Helper: create a Guzzle client with a mock handler.
     */
    private function createMockHttpClient(array $responses): ClientInterface
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

    // ─── Environment ───────────────────────────────────────────────────

    public function testInvalidEnvironmentThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid environment 'invalid_env'");
        new ZatcaAPI('invalid_env');
    }

    public function testValidEnvironments(): void
    {
        $this->assertStringContainsString('developer-portal', (new ZatcaAPI('sandbox'))->getBaseUri());
        $this->assertStringContainsString('simulation', (new ZatcaAPI('simulation'))->getBaseUri());
        $this->assertStringContainsString('core', (new ZatcaAPI('production'))->getBaseUri());
    }

    // ─── File Loading ──────────────────────────────────────────────────

    public function testLoadCSRFromFileSuccess(): void
    {
        $content = "test-csr-content";
        $tempFile = tempnam(sys_get_temp_dir(), 'csr_');
        file_put_contents($tempFile, $content);

        $api = new ZatcaAPI('sandbox');
        $this->assertEquals($content, $api->loadCSRFromFile($tempFile));

        unlink($tempFile);
    }

    public function testLoadCSRFromFileNotFound(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File not found');
        (new ZatcaAPI('sandbox'))->loadCSRFromFile('nonexistent_file.csr');
    }

    // ─── Compliance Certificate ────────────────────────────────────────

    public function testRequestComplianceCertificateSuccess(): void
    {
        $certContent = "MIICBDCCAaqgAwIBAgIGAZT...mock-cert-data";
        $responseData = [
            'binarySecurityToken' => base64_encode($certContent),
            'secret'              => 'Dehvg1fc8GF6Jwt5bOxXwC6enR93VxeNEo2mlUatfgw=',
            'requestID'           => '1234567890123',
        ];
        $client = $this->createMockHttpClient([
            new Response(200, [], json_encode($responseData))
        ]);

        $result = (new ZatcaAPI('sandbox', $client))
            ->requestComplianceCertificate('dummy-csr', '123123');

        $this->assertInstanceOf(ComplianceCertificateResult::class, $result);
        $this->assertEquals($certContent, $result->getCertificate());
        $this->assertEquals('Dehvg1fc8GF6Jwt5bOxXwC6enR93VxeNEo2mlUatfgw=', $result->getSecret());
        $this->assertEquals('1234567890123', $result->getRequestId());
    }

    public function testRequestComplianceCertificateToArray(): void
    {
        $responseData = [
            'binarySecurityToken' => base64_encode('cert'),
            'secret'              => 'secret123',
            'requestID'           => 'req123',
        ];
        $client = $this->createMockHttpClient([
            new Response(200, [], json_encode($responseData))
        ]);

        $result = (new ZatcaAPI('sandbox', $client))
            ->requestComplianceCertificate('csr', '000000');

        $arr = $result->toArray();
        $this->assertArrayHasKey('certificate', $arr);
        $this->assertArrayHasKey('secret', $arr);
        $this->assertArrayHasKey('requestId', $arr);
    }

    // ─── Production Certificate ────────────────────────────────────────

    public function testRequestProductionCertificateSuccess(): void
    {
        $certContent = "production-cert-data";
        $responseData = [
            'binarySecurityToken' => base64_encode($certContent),
            'secret'              => 'prodSecret',
            'requestID'           => 'prodReqID',
        ];
        $client = $this->createMockHttpClient([
            new Response(200, [], json_encode($responseData))
        ]);

        $result = (new ZatcaAPI('sandbox', $client))
            ->requestProductionCertificate('cert', 'secret', 'complianceReqId');

        $this->assertInstanceOf(ProductionCertificateResult::class, $result);
        $this->assertEquals($certContent, $result->getCertificate());
        $this->assertEquals('prodSecret', $result->getSecret());
        $this->assertEquals('prodReqID', $result->getRequestId());
    }

    // ─── Renew Production Certificate ──────────────────────────────────

    public function testRenewProductionCertificateSuccess(): void
    {
        $certContent = "renewed-cert-data";
        $responseData = [
            'binarySecurityToken' => base64_encode($certContent),
            'secret'              => 'newSecret',
            'requestID'           => 'renewReqID',
        ];
        $client = $this->createMockHttpClient([
            new Response(200, [], json_encode($responseData))
        ]);

        $result = (new ZatcaAPI('sandbox', $client))
            ->renewProductionCertificate('cert', 'secret', 'new-csr', '654321');

        $this->assertInstanceOf(ProductionCertificateResult::class, $result);
        $this->assertEquals($certContent, $result->getCertificate());
        $this->assertEquals('newSecret', $result->getSecret());
        $this->assertEquals('renewReqID', $result->getRequestId());
    }

    // ─── Reporting Invoice (B2C) ───────────────────────────────────────

    public function testSubmitReportingInvoiceSuccess(): void
    {
        $responseData = [
            'reportingStatus'   => 'REPORTED',
            'validationResults' => [
                'status'          => 'PASS',
                'infoMessages'    => [['message' => 'Invoice reported successfully']],
                'warningMessages' => [],
                'errorMessages'   => [],
            ],
        ];
        $client = $this->createMockHttpClient([
            new Response(200, [], json_encode($responseData))
        ]);

        $result = (new ZatcaAPI('sandbox', $client))
            ->submitReportingInvoice('cert', 'secret', '<Invoice>...</Invoice>', 'hash123', 'uuid-1');

        $this->assertInstanceOf(ReportingResponse::class, $result);
        $this->assertTrue($result->isReported());
        $this->assertEquals('REPORTED', $result->getReportingStatus());
        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->hasErrors());
        $this->assertEquals('PASS', $result->getValidationStatus());
        $this->assertNotEmpty($result->getInfoMessages());
    }

    public function testSubmitReportingInvoiceWithWarnings(): void
    {
        $responseData = [
            'reportingStatus'   => 'REPORTED',
            'validationResults' => [
                'status'          => 'WARNING',
                'infoMessages'    => [],
                'warningMessages' => [['message' => 'Minor issue found']],
                'errorMessages'   => [],
            ],
        ];
        $client = $this->createMockHttpClient([
            new Response(202, [], json_encode($responseData))
        ]);

        $result = (new ZatcaAPI('sandbox', $client))
            ->submitReportingInvoice('cert', 'secret', '<Invoice/>', 'hash', 'uuid');

        $this->assertInstanceOf(ReportingResponse::class, $result);
        $this->assertTrue($result->isReported());
        $this->assertTrue($result->hasWarnings());
        $this->assertFalse($result->hasErrors());
        $this->assertCount(1, $result->getWarningMessages());
    }

    // ─── Clearance Invoice (B2B) ───────────────────────────────────────

    public function testSubmitClearanceInvoiceSuccess(): void
    {
        $clearedXml = '<Invoice>cleared</Invoice>';
        $responseData = [
            'clearanceStatus'   => 'CLEARED',
            'clearedInvoice'    => base64_encode($clearedXml),
            'validationResults' => [
                'status'          => 'PASS',
                'infoMessages'    => [],
                'warningMessages' => [],
                'errorMessages'   => [],
            ],
        ];
        $client = $this->createMockHttpClient([
            new Response(200, [], json_encode($responseData))
        ]);

        $result = (new ZatcaAPI('sandbox', $client))
            ->submitClearanceInvoice('cert', 'secret', '<Invoice/>', 'hash', 'uuid');

        $this->assertInstanceOf(ClearanceResponse::class, $result);
        $this->assertTrue($result->isCleared());
        $this->assertEquals('CLEARED', $result->getClearanceStatus());
        $this->assertNotNull($result->getClearedInvoice());
        $this->assertEquals($clearedXml, $result->getDecodedClearedInvoice());
        $this->assertFalse($result->hasErrors());
    }

    // ─── Compliance Invoice ────────────────────────────────────────────

    public function testValidateInvoiceComplianceSuccess(): void
    {
        $responseData = [
            'reportingStatus'   => 'REPORTED',
            'clearanceStatus'   => null,
            'status'            => 'PASS',
            'validationResults' => [
                'status'          => 'PASS',
                'infoMessages'    => [],
                'warningMessages' => [],
                'errorMessages'   => [],
            ],
        ];
        $client = $this->createMockHttpClient([
            new Response(200, [], json_encode($responseData))
        ]);

        $result = (new ZatcaAPI('sandbox', $client))
            ->validateInvoiceCompliance('cert', 'secret', '<Invoice/>', 'hash', 'uuid');

        $this->assertInstanceOf(ComplianceInvoiceResponse::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('REPORTED', $result->getReportingStatus());
        $this->assertEquals('PASS', $result->getStatus());
    }

    // ─── Error Handling ────────────────────────────────────────────────

    public function testApiErrorThrowsException(): void
    {
        $client = $this->createMockHttpClient([
            new Response(400, [], json_encode(['error' => 'Bad Request']))
        ]);

        $this->expectException(ZatcaApiException::class);
        $this->expectExceptionMessage('ZATCA API error (HTTP 400)');

        (new ZatcaAPI('sandbox', $client))
            ->requestComplianceCertificate('csr', '000000');
    }

    public function testApiErrorContextContainsDetails(): void
    {
        $client = $this->createMockHttpClient([
            new Response(401, [], json_encode(['message' => 'Unauthorized']))
        ]);

        try {
            (new ZatcaAPI('sandbox', $client))
                ->requestComplianceCertificate('csr', '000000');
            $this->fail('Expected ZatcaApiException');
        } catch (ZatcaApiException $e) {
            $context = $e->getContext();
            $this->assertEquals(401, $context['statusCode']);
            $this->assertEquals('/compliance', $context['endpoint']);
            $this->assertArrayHasKey('response', $context);
        }
    }

    public function testInvalidJsonResponseThrowsException(): void
    {
        $client = $this->createMockHttpClient([
            new Response(200, [], 'not-json{{{')
        ]);

        $this->expectException(ZatcaApiException::class);
        $this->expectExceptionMessage('Failed to parse API response');

        (new ZatcaAPI('sandbox', $client))
            ->requestComplianceCertificate('csr', '000000');
    }

    // ─── Response Base Class ───────────────────────────────────────────

    public function testApiResponseToArray(): void
    {
        $responseData = [
            'clearanceStatus'   => 'CLEARED',
            'validationResults' => ['status' => 'PASS'],
        ];
        $client = $this->createMockHttpClient([
            new Response(200, [], json_encode($responseData))
        ]);

        $result = (new ZatcaAPI('sandbox', $client))
            ->submitClearanceInvoice('cert', 'secret', '<Invoice/>', 'hash', 'uuid');

        $arr = $result->toArray();
        $this->assertEquals('CLEARED', $arr['clearanceStatus']);
        $this->assertEquals('PASS', $arr['validationResults']['status']);
    }

    // ─── Save to JSON ──────────────────────────────────────────────────

    public function testSaveToJson(): void
    {
        // Reset any static base path that may have been set by other tests
        \Saleh7\Zatca\Storage::setBasePath('');

        $tempFile = tempnam(sys_get_temp_dir(), 'zatca_json_');

        $api = new ZatcaAPI('sandbox');
        $api->saveToJson('test-cert', 'test-secret', 'test-req-id', $tempFile);

        $this->assertFileExists($tempFile);
        $data = json_decode(file_get_contents($tempFile), true);
        $this->assertEquals('test-cert', $data['certificate']);
        $this->assertEquals('test-secret', $data['secret']);
        $this->assertEquals('test-req-id', $data['requestId']);

        unlink($tempFile);
    }
}
