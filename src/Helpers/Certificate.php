<?php

namespace Saleh7\Zatca\Helpers;

use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\EC;
use phpseclib3\File\X509;

/**
 * Certificate helper class.
 *
 * Provides methods to manage and use X509 certificates.
 *
 * @package Saleh7\Zatca\Helpers
 * @mixin X509
 */
class Certificate
{
    /**
     * The raw certificate content.
     *
     * @var string
     */
    protected string $rawCertificate;

    /**
     * The X509 certificate object.
     *
     * @var X509
     */
    protected X509 $x509;

    /**
     * The private key for this certificate.
     *
     * @var PrivateKey
     */
    protected PrivateKey $privateKey;

    /**
     * The secret key used for authentication.
     *
     * @var string
     */
    protected string $secretKey;

    /**
     * Constructor.
     *
     * @param string $rawCert         The raw certificate string.
     * @param string $privateKeyStr   The private key string.
     * @param string $secretKey The secret key.
     */
    public function __construct(string $rawCert, string $privateKeyStr, string $secretKey)
    {
        $this->secretKey = $secretKey;
        $this->rawCertificate = $rawCert;
        $this->x509 = new X509();
        $this->x509->loadX509($rawCert);
        $this->privateKey = EC::loadPrivateKey($privateKeyStr);
    }

    /**
     * Delegate method calls to the underlying X509 object.
     *
     * @param string $name       The method name.
     * @param array  $arguments  The method arguments.
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->x509->{$name}(...$arguments);
    }

    /**
     * Get the private key.
     *
     * @return PrivateKey
     */
    public function getPrivateKey(): PrivateKey
    {
        return $this->privateKey;
    }

    /**
     * Get the raw certificate content.
     *
     * @return string
     */
    public function getRawCertificate(): string
    {
        return $this->rawCertificate;
    }

    /**
     * Get the X509 certificate object.
     *
     * @return X509
     */
    public function getX509(): X509
    {
        return $this->x509;
    }

    /**
     * Create the authorization header using the raw certificate and secret key.
     *
     * @return string
     */
    public function getAuthHeader(): string
    {
        return 'Basic ' . base64_encode(base64_encode($this->getRawCertificate()) . ':' . $this->getSecretKey());
    }

    /**
     * Get the secret key.
     *
     * @return string|null
     */
    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    /**
     * Generate a hash of the certificate.
     *
     * @return string
     */
    public function getCertHash(): string
    {
        return base64_encode(hash('sha256', $this->rawCertificate));
    }

    /**
     * Get the formatted issuer details.
     *
     * @return string
     */
    public function getFormattedIssuer(): string
    {
        $dnArray = explode(
            ",",
            str_replace(
                ["0.9.2342.19200300.100.1.25", "/", ", "],
                ["DC", ",", ","],
                $this->x509->getIssuerDN(X509::DN_STRING)
            )
        );

        return implode(", ", array_reverse($dnArray));
    }
    /**
     * Get the raw public key in base64 format.
     *
     * @return string
     */
    public function getRawPublicKey(): string
    {
        return str_replace(
            ["-----BEGIN PUBLIC KEY-----\r\n", "\r\n-----END PUBLIC KEY-----", "\r\n"],
            '',
            $this->x509->getPublicKey()->toString('PKCS8')
        );
    }

    /**
     * Get the certificate signature.
     *
     * Note: Removes an extra prefix byte from the signature.
     *
     * @return string
     */
    public function getCertSignature(): string
    {
        return substr($this->getCurrentCert()['signature'], 1);
    }


}
