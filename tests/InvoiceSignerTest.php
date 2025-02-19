<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Saleh7\Zatca\InvoiceSigner;
use Saleh7\Zatca\Helpers\Certificate;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\Common\PublicKey;

/**
 * DummyCertificate simulates a certificate with fixed dummy values.
 */
class DummyCertificateSing extends Certificate {
    public function __construct() {
        // No initialization required for dummy values.
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
            
            public function withPassword($password = false) {
                return $this;
            }
            
            public function __toString(): string {
                return 'DUMMY_PRIVATE_KEY';
            }
            
            // Removed the string type-hint to match the interface signature.
            public function sign($message): string {
                return hash('sha256', $message, true); // Simulated signature
            }
            
            public function getPublicKey(): PublicKey {
                return new class implements PublicKey {
                    public function toString($format = 'PKCS8'): string {
                        return 'DUMMY_PUBLIC_KEY';
                    }
                    
                    public function __toString(): string {
                        return 'DUMMY_PUBLIC_KEY';
                    }
                };
            }
        };
    }
    
    public function getCertificateSignature(): string {
        return 'DUMMY_CERT_SIGNATURE';
    }
    
    public function getRawPublicKey(): string {
        return base64_encode('DUMMY_PUBLIC_KEY');
    }
}

/**
 * Test class for InvoiceSigner.
 */
class InvoiceSignerTest extends TestCase
{
    /**
     * Create a dummy certificate instance.
     *
     * @return Certificate
     */
    private function createDummyCertificate(): Certificate
    {
        return new DummyCertificateSing();
    }

    /**
     * Test that signInvoice() produces a signed invoice with expected output.
     */
    public function testSignInvoiceProducesValidOutput(): void
    {
        // Dummy invoice XML with required namespace declarations.
        $invoiceXml = <<<XML
<Invoice xmlns:cac="urn:oasis:names:specification:ubl:cac" 
         xmlns:cbc="urn:oasis:names:specification:ubl:cbc" 
         xmlns:ext="urn:oasis:names:specification:ubl:dsig:enveloped:xades">
    <cbc:ProfileID>TEST_PROFILE</cbc:ProfileID>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>Test Seller</cbc:RegistrationName>
            </cac:PartyLegalEntity>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>1234567890</cbc:CompanyID>
            </cac:PartyTaxScheme>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cbc:IssueDate>2021-01-01</cbc:IssueDate>
    <cbc:IssueTime>12:00:00</cbc:IssueTime>
    <cac:LegalMonetaryTotal>
        <cbc:TaxInclusiveAmount>100.00</cbc:TaxInclusiveAmount>
    </cac:LegalMonetaryTotal>
    <cac:TaxTotal>
        <cbc:TaxAmount>15.00</cbc:TaxAmount>
    </cac:TaxTotal>
    <cbc:InvoiceTypeCode name="01">01</cbc:InvoiceTypeCode>
    <ext:UBLExtensions>
        <dummy>Remove this</dummy>
    </ext:UBLExtensions>
    <cac:Signature>Remove this too</cac:Signature>
    <cac:AdditionalDocumentReference>
        <cbc:ID>QR</cbc:ID>
    </cac:AdditionalDocumentReference>
</Invoice>
XML;
        
        $dummyCert = $this->createDummyCertificate();
        $signer = InvoiceSigner::signInvoice($invoiceXml, $dummyCert);
        
        $signedInvoice = $signer->getInvoice();
        $hash = $signer->getHash();
        $qrCode = $signer->getQRCode();
        $cert = $signer->getCertificate();
        
        // Check that the signed invoice is not empty and contains expected tags.
        $this->assertNotEmpty($signedInvoice, 'Signed invoice should not be empty.');
        $this->assertStringContainsString('<ext:UBLExtensions>', $signedInvoice, 'Signed invoice should contain UBLExtensions.');
        $this->assertStringContainsString('<cac:AdditionalDocumentReference>', $signedInvoice, 'Signed invoice should contain the QR Code node.');
        // Check that hash and QR code are generated.
        $this->assertNotEmpty($hash, 'Invoice hash should not be empty.');
        $this->assertNotEmpty($qrCode, 'QR Code should not be empty.');
        // Check that the returned certificate is our dummy.
        $this->assertSame($dummyCert, $cert, 'Returned certificate should match the one provided.');
    }
}
