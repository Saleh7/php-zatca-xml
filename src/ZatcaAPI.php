<?php

namespace Saleh7\Zatca;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Saleh7\Zatca\Api\ApiResponse;
use Saleh7\Zatca\Api\ClearanceResponse;
use Saleh7\Zatca\Api\ComplianceCertificateResult;
use Saleh7\Zatca\Api\ComplianceInvoiceResponse;
use Saleh7\Zatca\Api\ProductionCertificateResult;
use Saleh7\Zatca\Api\ReportingResponse;
use Saleh7\Zatca\Exceptions\ZatcaApiException;
use Saleh7\Zatca\Exceptions\ZatcaStorageException;
use InvalidArgumentException;

/**
 * ZATCA E-Invoicing API Client.
 *
 * Covers the full ZATCA e-invoicing API lifecycle:
 *
 * 1. Compliance Certificate   → POST /compliance
 * 2. Compliance Invoice Check → POST /compliance/invoices
 * 3. Production Certificate   → POST /production/csids
 * 4. Production CSID Renewal  → PATCH /production/csids
 * 5. Reporting Invoice (B2C)  → POST /invoices/reporting/single
 * 6. Clearance Invoice (B2B)  → POST /invoices/clearance/single
 *
 * @see https://zatca.gov.sa/en/E-Invoicing/Introduction/Guidelines/Documents/ZATCA_FATOORA_Portal_User_Guide.pdf
 */
class ZatcaAPI
{
    /**
     * ZATCA API base URLs per environment.
     *
     * sandbox    → Developer portal for integration testing
     * simulation → Pre-production simulation environment
     * production → Live production environment
     */
    private const ENVIRONMENTS = [
        'sandbox'    => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal',
        'simulation' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation',
        'production' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core',
    ];

    private const API_VERSION = 'V2';

    private ClientInterface $httpClient;
    private string $environment;

    /**
     * @param string               $environment API environment (sandbox|simulation|production)
     * @param ClientInterface|null $client      Optional HTTP client for dependency injection
     * @throws InvalidArgumentException For invalid environment
     */
    public function __construct(string $environment = 'sandbox', ?ClientInterface $client = null)
    {
        if (!isset(self::ENVIRONMENTS[$environment])) {
            $validEnvs = implode(', ', array_keys(self::ENVIRONMENTS));
            throw new InvalidArgumentException("Invalid environment '$environment'. Valid options: $validEnvs");
        }
        $this->environment = $environment;
        $this->httpClient  = $client ?? new Client([
            'base_uri' => $this->getBaseUri(),
            'timeout'  => 30,
            'verify'   => true,
        ]);
    }

    /**
     * Get the base URI for the current environment.
     */
    public function getBaseUri(): string
    {
        return self::ENVIRONMENTS[$this->environment];
    }

    // ─── Certificate Lifecycle ─────────────────────────────────────────

    /**
     * Step 1: Request a Compliance Certificate (CCSID).
     *
     * Submits a CSR with an OTP to obtain a compliance certificate and secret
     * for use during the compliance check phase.
     *
     * Endpoint: POST /compliance
     *
     * @param string $csr CSR content (PEM format, will be base64-encoded)
     * @param string $otp One-Time Password from ZATCA portal
     * @return ComplianceCertificateResult Certificate, secret, and request ID
     * @throws ZatcaApiException
     */
    public function requestComplianceCertificate(string $csr, string $otp): ComplianceCertificateResult
    {
        $response = $this->sendRequest(
            'POST',
            '/compliance',
            ['OTP' => $otp],
            ['csr' => base64_encode($csr)]
        );

        return new ComplianceCertificateResult(
            $this->decodeCertificate($response['binarySecurityToken'] ?? ''),
            $response['secret'] ?? '',
            $response['requestID'] ?? ''
        );
    }

    /**
     * Step 2: Request a Production Certificate (PCSID).
     *
     * After passing compliance checks, exchange the compliance certificate
     * for a production certificate.
     *
     * Endpoint: POST /production/csids
     *
     * @param string $certificate Compliance certificate for authentication
     * @param string $secret      Compliance secret for authentication
     * @param string $complianceRequestId Request ID from compliance certificate response
     * @return ProductionCertificateResult Production certificate, secret, and request ID
     * @throws ZatcaApiException
     */
    public function requestProductionCertificate(
        string $certificate,
        string $secret,
        string $complianceRequestId
    ): ProductionCertificateResult {
        $response = $this->sendRequest(
            'POST',
            '/production/csids',
            [],
            ['compliance_request_id' => $complianceRequestId],
            $this->buildAuthHeader($certificate, $secret)
        );

        return new ProductionCertificateResult(
            $this->decodeCertificate($response['binarySecurityToken'] ?? ''),
            $response['secret'] ?? '',
            $response['requestID'] ?? ''
        );
    }

    /**
     * Renew a Production Certificate (PCSID).
     *
     * Used to renew an expiring production certificate by submitting a new CSR
     * with an OTP, authenticated with the current production credentials.
     *
     * Endpoint: PATCH /production/csids
     *
     * @param string $certificate Current production certificate for authentication
     * @param string $secret      Current production secret for authentication
     * @param string $csr         New CSR content (PEM format, will be base64-encoded)
     * @param string $otp         One-Time Password from ZATCA portal
     * @return ProductionCertificateResult New production certificate, secret, and request ID
     * @throws ZatcaApiException
     */
    public function renewProductionCertificate(
        string $certificate,
        string $secret,
        string $csr,
        string $otp
    ): ProductionCertificateResult {
        $response = $this->sendRequest(
            'PATCH',
            '/production/csids',
            ['OTP' => $otp],
            ['csr' => base64_encode($csr)],
            $this->buildAuthHeader($certificate, $secret)
        );

        return new ProductionCertificateResult(
            $this->decodeCertificate($response['binarySecurityToken'] ?? ''),
            $response['secret'] ?? '',
            $response['requestID'] ?? ''
        );
    }

    // ─── Invoice Operations ────────────────────────────────────────────

    /**
     * Submit a Reporting Invoice (B2C / Simplified).
     *
     * Simplified (B2C) invoices are reported to ZATCA asynchronously.
     * They do not require clearance but must still be reported.
     *
     * Endpoint: POST /invoices/reporting/single
     *
     * @param string $certificate    Production certificate for authentication
     * @param string $secret         Production secret for authentication
     * @param string $signedInvoice  Signed invoice XML content
     * @param string $invoiceHash    Invoice hash (SHA-256, base64-encoded)
     * @param string $uuid           Unique invoice identifier (UUID v4)
     * @return ReportingResponse
     * @throws ZatcaApiException
     */
    public function submitReportingInvoice(
        string $certificate,
        string $secret,
        string $signedInvoice,
        string $invoiceHash,
        string $uuid
    ): ReportingResponse {
        $data = $this->sendRequest(
            'POST',
            '/invoices/reporting/single',
            ['Clearance-Status' => '0'],
            [
                'invoiceHash' => $invoiceHash,
                'uuid'        => $uuid,
                'invoice'     => base64_encode($signedInvoice),
            ],
            $this->buildAuthHeader($certificate, $secret)
        );

        return new ReportingResponse($data, $this->lastStatusCode);
    }

    /**
     * Submit a Clearance Invoice (B2B / Standard).
     *
     * Standard (B2B) invoices require real-time clearance from ZATCA.
     * The response includes the cleared (stamped) invoice XML.
     *
     * Endpoint: POST /invoices/clearance/single
     *
     * @param string $certificate    Production certificate for authentication
     * @param string $secret         Production secret for authentication
     * @param string $signedInvoice  Signed invoice XML content
     * @param string $invoiceHash    Invoice hash (SHA-256, base64-encoded)
     * @param string $uuid           Unique invoice identifier (UUID v4)
     * @return ClearanceResponse
     * @throws ZatcaApiException
     */
    public function submitClearanceInvoice(
        string $certificate,
        string $secret,
        string $signedInvoice,
        string $invoiceHash,
        string $uuid
    ): ClearanceResponse {
        $data = $this->sendRequest(
            'POST',
            '/invoices/clearance/single',
            ['Clearance-Status' => '1'],
            [
                'invoiceHash' => $invoiceHash,
                'uuid'        => $uuid,
                'invoice'     => base64_encode($signedInvoice),
            ],
            $this->buildAuthHeader($certificate, $secret)
        );

        return new ClearanceResponse($data, $this->lastStatusCode);
    }

    /**
     * Validate invoice compliance.
     *
     * Used during the compliance check phase (before requesting production certificate)
     * to verify that invoices meet ZATCA requirements.
     *
     * Endpoint: POST /compliance/invoices
     *
     * @param string $certificate    Compliance certificate for authentication
     * @param string $secret         Compliance secret for authentication
     * @param string $signedInvoice  Signed invoice XML content
     * @param string $invoiceHash    Invoice hash (SHA-256, base64-encoded)
     * @param string $uuid           Unique invoice identifier (UUID v4)
     * @return ComplianceInvoiceResponse
     * @throws ZatcaApiException
     */
    public function validateInvoiceCompliance(
        string $certificate,
        string $secret,
        string $signedInvoice,
        string $invoiceHash,
        string $uuid
    ): ComplianceInvoiceResponse {
        $data = $this->sendRequest(
            'POST',
            '/compliance/invoices',
            [],
            [
                'invoiceHash' => $invoiceHash,
                'uuid'        => $uuid,
                'invoice'     => base64_encode($signedInvoice),
            ],
            $this->buildAuthHeader($certificate, $secret)
        );

        return new ComplianceInvoiceResponse($data, $this->lastStatusCode);
    }

    // ─── Utilities ─────────────────────────────────────────────────────

    /**
     * Load CSR content from a file.
     *
     * @param string $path File path to the CSR
     * @return string CSR content
     * @throws \Exception If file is not found or unreadable
     */
    public function loadCSRFromFile(string $path): string
    {
        if (!file_exists($path)) {
            throw new \Exception("File not found: {$path}");
        }
        $content = file_get_contents($path);
        if ($content === false) {
            throw new \Exception("Could not read file: {$path}");
        }
        return $content;
    }

    /**
     * Save certificate data to a JSON file for later use.
     *
     * @param string $certificate The certificate string
     * @param string $secret      The API secret
     * @param string $requestId   The request ID
     * @param string $filePath    Path to save the JSON file
     * @throws \Exception If JSON encoding fails
     * @throws ZatcaStorageException If file cannot be written
     */
    public function saveToJson(string $certificate, string $secret, string $requestId, string $filePath): void
    {
        $data = [
            'certificate' => $certificate,
            'secret'      => $secret,
            'requestId'   => $requestId,
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \Exception("Failed to encode data to JSON: " . json_last_error_msg());
        }

        (new Storage)->put($filePath, $json);
    }

    // ─── Internal ──────────────────────────────────────────────────────

    /** @var int Last HTTP status code from sendRequest */
    private int $lastStatusCode = 0;

    /**
     * Build Basic auth header from certificate and secret.
     *
     * ZATCA auth format: Basic base64( base64(certificate) : secret )
     *
     * The certificate is stored as base64_decode(binarySecurityToken).
     * Re-encoding it with base64 reconstructs the original token for auth.
     */
    private function buildAuthHeader(string $certificate, string $secret): array
    {
        $credentials = base64_encode(base64_encode(trim($certificate)) . ':' . trim($secret));
        return ['Authorization' => 'Basic ' . $credentials];
    }

    /**
     * Decode a binarySecurityToken from ZATCA's response.
     *
     * ZATCA returns certificates as base64-encoded strings in the binarySecurityToken field.
     */
    private function decodeCertificate(string $base64Token): string
    {
        return base64_decode($base64Token);
    }

    /**
     * Send an HTTP request to the ZATCA API.
     *
     * @param string $method      HTTP method (POST, PATCH)
     * @param string $endpoint    API endpoint path
     * @param array  $headers     Additional request headers
     * @param array  $payload     Request body (JSON-encoded)
     * @param array  $authHeaders Authentication headers
     * @return array Decoded response data
     * @throws ZatcaApiException On HTTP or API errors
     */
    private function sendRequest(
        string $method,
        string $endpoint,
        array $headers = [],
        array $payload = [],
        array $authHeaders = []
    ): array {
        try {
            $mergedHeaders = array_merge(
                [
                    'Accept-Version' => self::API_VERSION,
                    'Accept'         => 'application/json',
                    'Content-Type'   => 'application/json',
                    'Accept-Language' => 'en',
                ],
                $headers,
                $authHeaders
            );

            $options = [
                'headers'     => $mergedHeaders,
                'json'        => $payload,
                'http_errors' => false, // Don't throw on 4xx/5xx, we handle it
            ];

            $url = $this->getBaseUri() . $endpoint;

            $response = $this->httpClient->request($method, $url, $options);
            $this->lastStatusCode = $response->getStatusCode();

            $data = $this->parseResponse($response);

            // 200 = success, 202 = accepted with warnings
            if ($this->lastStatusCode >= 300) {
                throw new ZatcaApiException(
                    sprintf('ZATCA API error (HTTP %d) on %s %s', $this->lastStatusCode, $method, $endpoint),
                    [
                        'endpoint'   => $endpoint,
                        'statusCode' => $this->lastStatusCode,
                        'response'   => $data,
                    ]
                );
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new ZatcaApiException('HTTP request failed', [
                'message'  => $e->getMessage(),
                'endpoint' => $endpoint,
            ], $e->getCode(), $e);
        }
    }

    /**
     * Parse the JSON response body.
     *
     * @throws ZatcaApiException If JSON is invalid
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $content = $response->getBody()->getContents();

        if (trim($content) === '') {
            return [];
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ZatcaApiException('Failed to parse API response: ' . json_last_error_msg(), [
                'content'    => substr($content, 0, 1024),
                'statusCode' => $response->getStatusCode(),
            ]);
        }

        return $data;
    }
}
