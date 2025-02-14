<?php
use PHPUnit\Framework\TestCase;
use Saleh7\Zatca\Helpers\InvoiceSignatureBuilder;
use Saleh7\Zatca\Helpers\Certificate;
use phpseclib3\Crypt\Common\PrivateKey;

/**
 * DummyCertificate is a simple subclass of Certificate returning fixed dummy values.
 */
class DummyCertificate extends Certificate {
    // Optionally bypass parent's constructor.
    public function __construct() {
        // No initialization needed for dummy values.
    }
    
    public function getRawCertificate(): string {
         return 'DUMMY_RAW_CERT';
    }
    
    public function getCertHash(): string {
         return 'DUMMY_CERT_HASH';
    }
    
    public function getFormattedIssuer(): string {
         return 'DUMMY_ISSUER';
    }
    
    public function getCurrentCert(): array {
         return [
            'tbsCertificate' => [
                'serialNumber' => new class {
                    public function toString(): string {
                        return 'DUMMY_SERIAL';
                    }
                }
            ]
         ];
    }
    
    public function getPrivateKey(): PrivateKey {
         return new class implements PrivateKey {
              public function toString($format = 'PKCS8', $password = null): string {
                  return 'DUMMY_PRIVATE_KEY';
              }
              public function withPassword($password) {
                  return $this;
              }
              public function __toString(): string {
                  return 'DUMMY_PRIVATE_KEY';
              }
         };
    }
    
    public function getCertificateSignature(): string {
         return 'DUMMY_CERT_SIGNATURE';
    }
}

/**
 * Test class for InvoiceSignatureBuilder.
 */
class InvoiceSignatureBuilderTest extends TestCase
{
    /**
     * Create a dummy certificate instance.
     *
     * @return Certificate
     */
    private function createDummyCertificate(): Certificate
    {
         return new DummyCertificate();
    }

    /**
     * Test that buildSignatureXml() returns valid XML containing key parts.
     */
    public function testBuildSignatureXmlReturnsValidXml(): void
    {
         $dummyCert = $this->createDummyCertificate();
         $invoiceDigest = 'DUMMY_INVOICE_DIGEST';
         $signatureValue = 'DUMMY_SIGNATURE_VALUE';

         $builder = new InvoiceSignatureBuilder();
         $builder->setCertificate($dummyCert)
                 ->setInvoiceDigest($invoiceDigest)
                 ->setSignatureValue($signatureValue);

         $xmlString = $builder->buildSignatureXml();

         $this->assertNotEmpty($xmlString, 'XML string should not be empty.');
         $this->assertStringContainsString('ext:UBLExtension', $xmlString, 'XML should contain UBLExtension tag.');
         $this->assertStringContainsString('ds:SignatureValue', $xmlString, 'XML should contain SignatureValue tag.');
         $this->assertStringContainsString($signatureValue, $xmlString, 'XML should include our signature value.');
         $this->assertStringContainsString('DUMMY_RAW_CERT', $xmlString, 'XML should include the raw certificate.');
         $this->assertStringContainsString('DUMMY_CERT_HASH', $xmlString, 'XML should include the certificate hash.');
         $this->assertStringContainsString('DUMMY_ISSUER', $xmlString, 'XML should include the issuer name.');
         $this->assertStringContainsString('DUMMY_SERIAL', $xmlString, 'XML should include the certificate serial number.');
    }

    /**
     * Test that setter methods return self for method chaining.
     */
    public function testSettersReturnSelf(): void
    {
         $dummyCert = $this->createDummyCertificate();
         $builder = new InvoiceSignatureBuilder();

         $this->assertSame($builder, $builder->setCertificate($dummyCert));
         $this->assertSame($builder, $builder->setInvoiceDigest('TEST_DIGEST'));
         $this->assertSame($builder, $builder->setSignatureValue('TEST_SIGNATURE'));
    }
}
