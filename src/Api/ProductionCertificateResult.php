<?php

namespace Saleh7\Zatca\Api;

/**
 * Holds the production certificate response data (PCSID).
 *
 * Returned by ZatcaAPI::requestProductionCertificate() and
 * ZatcaAPI::renewProductionCertificate().
 *
 * Use these credentials for submitting reporting and clearance invoices.
 */
final class ProductionCertificateResult
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
     * Get the production certificate (decoded from binarySecurityToken).
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
     * Get the production request ID.
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
