<?php

namespace Saleh7\Zatca\Api;

/**
 * Class ComplianceCertificateResult
 *
 * Holds the compliance certificate response data.
 */
class ComplianceCertificateResult
{
    private string $certificate;
    private string $secret;
    private string $requestId;

    public function __construct(string $certificate, string $secret, string $requestId)
    {
        $this->certificate = $certificate;
        $this->secret = $secret;
        $this->requestId = $requestId;
    }

    public function getCertificate(): string
    {
        return $this->certificate;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }
}