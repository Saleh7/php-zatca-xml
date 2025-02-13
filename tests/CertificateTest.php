<?php

use PHPUnit\Framework\TestCase;
use Saleh7\Zatca\Helpers\Certificate;
use phpseclib3\File\X509;
use phpseclib3\Crypt\Common\PrivateKey;

/**
 * Test class for the Certificate helper.
 */
class CertificateTest extends TestCase
{
    /**
     * Sample certificate string from ZATCA.
     *
     * @var string
     */
    protected $certificateData = 'MIICAzCCAaqgAwIBAgIGAZT7anBcMAoGCCqGSM49BAMCMBUxEzARBgNVBAMMCmVJbnZvaWNpbmcwHhcNMjUwMjEyMTgyNzE5WhcNMzAwMjExMjEwMDAwWjBUMRgwFgYDVQQDDA9NeSBPcmdhbml6YXRpb24xEzARBgNVBAoMCk15IENvbXBhbnkxFjAUBgNVBAsMDUlUIERlcGFydG1lbnQxCzAJBgNVBAYTAlNBMFYwEAYHKoZIzj0CAQYFK4EEAAoDQgAEdg+fe1K42qCMlH8MQmxi02RzKU3SfNHA5QUTh9ub6vqiTvY5ON0Q3CjBJ2qzrCeBguijyQQCFARDulpKaWAaW6OBqTCBpjAMBgNVHRMBAf8EAjAAMIGVBgNVHREEgY0wgYqkgYcwgYQxIDAeBgNVBAQMFzEtU2FsZWh8Mi0xbnwzLVNNRTAwMDIzMR8wHQYKCZImiZPyLGQBAQwPMzEyMzQ1Njc4OTAxMjMzMQ0wCwYDVQQMDAQxMTAwMRswGQYDVQQaDBJSaXlhZGggMTIzNCBTdHJlZXQxEzARBgNVBA8MClRlY2hub2xvZ3kwCgYIKoZIzj0EAwIDRwAwRAIgINT+MFQefLLdd7Jlayr8nZq1lQrXQgKYxuA14LRoDvUCIGVS+MserlYamKvlCtk/g9J4gPWoJMXygSGp7FTPV8e4';

    /**
     * Sample private key string generated during the initial certificate request.
     *
     * @var string
     */
    protected $privateKeyData = 'MIGEAgEAMBAGByqGSM49AgEGBSuBBAAKBG0wawIBAQQgPsPX88rLECL/346pDroiltt9ZFz8arMlt3FHeqdxaD6hRANCAAR2D597UrjaoIyUfwxCbGLTZHMpTdJ80cDlBROH25vq+qJO9jk43RDcKMEnarOsJ4GC6KPJBAIUBEO6WkppYBpb';

    /**
     * Sample secret key provided by ZATCA.
     *
     * @var string
     */
    protected $secretKeyData = '7v6ZNNZ31NS/ibZImnxSmMGWRRAXvI2qqkv4XF9jjs0=';

    /**
     * Instance of the Certificate class.
     *
     * @var Certificate
     */
    protected $certificate;

    /**
     * Set up the Certificate instance before each test.
     */
    protected function setUp(): void
    {
        $this->certificate = new Certificate(
            $this->certificateData,
            $this->privateKeyData,
            $this->secretKeyData
        );
    }

    /**
     * Test that getRawCertificate returns the correct certificate data.
     */
    public function testGetRawCertificate()
    {
        $this->assertEquals(
            $this->certificateData,
            $this->certificate->getRawCertificate(),
            'Raw certificate should match the input certificate data.'
        );
    }

    /**
     * Test that getX509 returns an instance of X509.
     */
    public function testGetX509()
    {
        $this->assertInstanceOf(
            X509::class,
            $this->certificate->getX509(),
            'getX509 should return an instance of X509.'
        );
    }

    /**
     * Test that getPrivateKey returns an instance of PrivateKey.
     */
    public function testGetPrivateKey()
    {
        $this->assertInstanceOf(
            PrivateKey::class,
            $this->certificate->getPrivateKey(),
            'getPrivateKey should return an instance of PrivateKey.'
        );
    }

    /**
     * Test that getSecretKey returns the correct secret key.
     */
    public function testGetSecretKey()
    {
        $this->assertEquals(
            $this->secretKeyData,
            $this->certificate->getSecretKey(),
            'Secret key should match the input secret key.'
        );
    }

    /**
     * Test that getAuthHeader returns a properly formatted authorization header.
     */
    public function testGetAuthHeader()
    {
        $authHeader = $this->certificate->getAuthHeader();
        $this->assertStringStartsWith(
            'Basic ',
            $authHeader,
            'Authorization header should start with "Basic ".'
        );
        $this->assertNotEmpty($authHeader, 'Authorization header should not be empty.');
    }

    /**
     * Test that getCertHash returns a valid base64 encoded SHA-256 hash.
     */
    public function testGetCertHash()
    {
        $hash = $this->certificate->getCertHash();
        $this->assertNotEmpty($hash, 'Certificate hash should not be empty.');
        
        // Decode and verify the length of the hash (SHA-256 produces 32 bytes).
        $decoded = base64_decode($hash, true);
        $this->assertEquals(
            32,
            strlen($decoded),
            'Decoded certificate hash should be 32 bytes long.'
        );
    }

    /**
     * Test that getFormattedIssuer returns a non-empty string.
     */
    public function testGetFormattedIssuer()
    {
        $issuer = $this->certificate->getFormattedIssuer();
        $this->assertIsString($issuer, 'Formatted issuer should be a string.');
        $this->assertNotEmpty($issuer, 'Formatted issuer should not be empty.');
        
        // Optionally, check if the issuer contains an expected value.
        $this->assertStringContainsString(
            'eInvoicing',
            $issuer,
            'Issuer details should contain "eInvoicing".'
        );
    }

    /**
     * Test that getRawPublicKey returns a public key in base64 format without headers.
     */
    public function testGetRawPublicKey()
    {
        $publicKey = $this->certificate->getRawPublicKey();
        $this->assertIsString($publicKey, 'Raw public key should be a string.');
        $this->assertNotEmpty($publicKey, 'Raw public key should not be empty.');
        $this->assertStringNotContainsString(
            '-----BEGIN PUBLIC KEY-----',
            $publicKey,
            'Raw public key should not contain header text.'
        );
    }

    /**
     * Test that getCertSignature returns the certificate signature without the extra prefix.
     */
    public function testGetCertSignature()
    {
        // Get the full signature from the current certificate array via delegation.
        $currentCert = $this->certificate->getCurrentCert();
        $fullSignature = $currentCert['signature'];
        
        // Get the processed signature from the method.
        $signature = $this->certificate->getCertSignature();
        $this->assertIsString($signature, 'Certificate signature should be a string.');
        $this->assertNotEmpty($signature, 'Certificate signature should not be empty.');
        
        // Check that the returned signature length is one less than the full signature.
        $this->assertEquals(
            strlen($fullSignature) - 1,
            strlen($signature),
            'Certificate signature should have one less byte than the full signature.'
        );
    }
}
