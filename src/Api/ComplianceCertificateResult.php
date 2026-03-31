<?php

namespace Saleh7\Zatca\Api;

/**
 * Holds the compliance certificate response data (CCSID).
 *
 * Returned by ZatcaAPI::requestComplianceCertificate().
 * Use these credentials for compliance invoice validation and
 * to request a production certificate.
 */
final class ComplianceCertificateResult
{
    private string $certificate;
    private string $secret;
    private string $requestId;

    public function __construct(string $certificate, string $secret, string $requestId)
    {
        $this->certificate = $certificate;
        $this->secret      = $secret;
        $this->requestId   = $requestId;
    }

    /**
     * Get the compliance certificate (decoded from binarySecurityToken).
     */
    public function getCertificate(): string
    {
        return $this->certificate;
    }

    /**
     * Get the secret key for API authentication.
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Get the compliance request ID.
     *
     * Pass this to ZatcaAPI::requestProductionCertificate() after compliance checks.
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * Export credentials as an associative array.
     */
    public function toArray(): array
    {
        return [
            'certificate' => $this->certificate,
            'secret'      => $this->secret,
            'requestId'   => $this->requestId,
        ];
    }
}
