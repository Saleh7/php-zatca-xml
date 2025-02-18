<?php

namespace Saleh7\Zatca\Helpers;

use Saleh7\Zatca\Helpers\Certificate;
use InvalidArgumentException;

/**
 * Class InvoiceSignatureBuilder
 *
 * Builds the UBL signature XML for invoices.
 *
 * @package Saleh7\Zatca\Helpers
 */
class InvoiceSignatureBuilder
{
    public const SAC = 'urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2';
    public const SBC = 'urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2';
    public const SIG = 'urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2';

    /**
     * @var Certificate
     */
    protected Certificate $cert;

    /**
     * @var string
     */
    protected string $invoiceDigest;

    /**
     * @var string
     */
    protected string $signatureValue;

    /**
     * Builds and returns the UBL signature XML as a formatted string.
     *
     * @return string The formatted UBL signature XML.
     *
     * @throws \DOMException
     */
    public function buildSignatureXml(): string
    {
        // Get current date and time in ISO8601 format.
        $signingTime = date('Y-m-d') . 'T' . date('H:i:s');

        // Create the signed properties XML.
        $signedPropertiesXml = $this->createSignedPropertiesXml($signingTime);

        // Create the UBLExtension element.
        $extensionXml = InvoiceExtension::newInstance("ext:UBLExtension");
        $extensionXml->addChild('ext:ExtensionURI', 'urn:oasis:names:specification:ubl:dsig:enveloped:xades');
        $extensionContent = $extensionXml->addChild('ext:ExtensionContent');
        $signatureDetails = $extensionContent->addChild('sig:UBLDocumentSignatures', null, [
            'xmlns:sig' => self::SIG,
            'xmlns:sac' => self::SAC,
            'xmlns:sbc' => self::SBC,
        ]);

        // Build the signature information.
        $signatureContent = $signatureDetails->addChild('sac:SignatureInformation');
        $signatureContent->addChild('cbc:ID', 'urn:oasis:names:specification:ubl:signature:1');
        $signatureContent->addChild('sbc:ReferencedSignatureID', 'urn:oasis:names:specification:ubl:signature:Invoice');

        // Build the ds:Signature element.
        $dsSignature = $signatureContent->addChild('ds:Signature', null, [
            'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
            'Id'        => 'signature',
        ]);

        $signedInfo = $dsSignature->addChild('ds:SignedInfo');
        $signedInfo->addChild('ds:CanonicalizationMethod', null, [
            'Algorithm' => 'http://www.w3.org/2006/12/xml-c14n11',
        ]);
        $signedInfo->addChild('ds:SignatureMethod', null, [
            'Algorithm' => 'http://www.w3.org/2001/04/xmldsig-more#ecdsa-sha256',
        ]);

        // Build reference element for invoice signed data.
        $reference = $signedInfo->addChild('ds:Reference', null, [
            'Id'  => 'invoiceSignedData',
            'URI' => '',
        ]);

        $transforms = $reference->addChild('ds:Transforms');
        // Exclude UBLExtensions.
        $xpath = $transforms->addChild('ds:Transform', null, [
            'Algorithm' => "http://www.w3.org/TR/1999/REC-xpath-19991116",
        ]);
        $xpath->addChild('ds:XPath', 'not(//ancestor-or-self::ext:UBLExtensions)');
        // Exclude cac:Signature.
        $xpath = $transforms->addChild('ds:Transform', null, [
            'Algorithm' => "http://www.w3.org/TR/1999/REC-xpath-19991116",
        ]);
        $xpath->addChild('ds:XPath', 'not(//ancestor-or-self::cac:Signature)');
        // Exclude AdditionalDocumentReference with ID "QR".
        $xpath = $transforms->addChild('ds:Transform', null, [
            'Algorithm' => "http://www.w3.org/TR/1999/REC-xpath-19991116",
        ]);
        $xpath->addChild('ds:XPath', "not(//ancestor-or-self::cac:AdditionalDocumentReference[cbc:ID='QR'])");
        // Canonicalization transform.
        $transforms->addChild('ds:Transform', null, [
            'Algorithm' => "http://www.w3.org/2006/12/xml-c14n11",
        ]);

        // Digest method and value for invoice data.
        $reference->addChild('ds:DigestMethod', null, [
            'Algorithm' => "http://www.w3.org/2001/04/xmlenc#sha256",
        ]);
        $reference->addChild('ds:DigestValue', $this->invoiceDigest);

        // Add a second reference for the signed properties.
        $propsReference = $signedInfo->addChild('ds:Reference', null, [
            'Type' => "http://www.w3.org/2000/09/xmldsig#SignatureProperties",
            'URI'  => "#xadesSignedProperties",
        ]);
        $propsReference->addChild('ds:DigestMethod', null, [
            'Algorithm' => "http://www.w3.org/2001/04/xmlenc#sha256",
        ]);
        $propsReference->addChild('ds:DigestValue', base64_encode(hash('sha256', $signedPropertiesXml)));

        // Add the signature value.
        $dsSignature->addChild('ds:SignatureValue', $this->signatureValue);

        // Add key info with the certificate.
        $keyInfo = $dsSignature->addChild('ds:KeyInfo');
        $x509Data = $keyInfo->addChild('ds:X509Data');
        $x509Data->addChild('ds:X509Certificate', $this->cert->getEncodedCertificate());

        // Build the ds:Object with qualifying properties.
        $dsObject = $dsSignature->addChild('ds:Object');
        $this->createSignatureObject($dsObject, $signingTime);

        // Remove the XML declaration and return formatted XML.
        $formattedXml = preg_replace('!^[^>]+>(\r\n|\n)!', '', $extensionXml->toXml());

        // Remove extra attributes and nodes added during UBLExtension creation.
        $formattedXml = str_replace(
            [
                ' xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" xmlns:ds="http://www.w3.org/2000/09/xmldsig#"',
                '<ext:UBLExtension xmlns:sig="urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xades="http://uri.etsi.org/01903/v1.3.2#">'
            ],
            [
                '',
                '<ext:UBLExtension>'
            ],
            $formattedXml
        );

        // Ensure proper indentation.
        return preg_replace('/^[ ]+(?=<)/m', '$0$0', $formattedXml);
    }

    /**
     * Creates the signature object XML.
     *
     * @param mixed  $dsObject   The ds:Object element.
     * @param string $signingTime The signing time.
     *
     * @return void
     */
    private function createSignatureObject($dsObject, string $signingTime): void
    {
        $qualProps = $dsObject->addChild('xades:QualifyingProperties', null, [
            'xmlns:xades' => "http://uri.etsi.org/01903/v1.3.2#",
            'Target'      => "signature",
        ]);

        $signedProps = $qualProps->addChild('xades:SignedProperties', null, [
            'xmlns:xades' => "http://uri.etsi.org/01903/v1.3.2#",
            'Id'          => 'xadesSignedProperties',
        ])->addChild('xades:SignedSignatureProperties');

        // Add the signing time.
        $signedProps->addChild('xades:SigningTime', $signingTime);
        // Add signing certificate details.
        $signingCert = $signedProps->addChild('xades:SigningCertificate');
        $certNode = $signingCert->addChild('xades:Cert');

        // Add certificate digest.
        $certDigest = $certNode->addChild('xades:CertDigest');
        $certDigest->addChild('ds:DigestMethod', null, [
            'Algorithm' => "http://www.w3.org/2001/04/xmlenc#sha256",
        ]);
        $certDigest->addChild('ds:DigestValue', $this->cert->getCertHash());

        // Add issuer serial.
        $issuerSerial = $certNode->addChild('xades:IssuerSerial');
        $issuerSerial->addChild('ds:X509IssuerName', $this->cert->getFormattedIssuer());
        $issuerSerial->addChild('ds:X509SerialNumber', $this->cert->getCurrentCert()['tbsCertificate']['serialNumber']->toString());
    }

    /**
     * Creates the signed properties XML string.
     *
     * Note: Do not alter the spacing as it may cause digest mismatches.
     *
     * @param string $signingTime The signing time.
     *
     * @return string The signed properties XML.
     */
    private function createSignedPropertiesXml(string $signingTime): string
    {
        $template = '<xades:SignedProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="xadesSignedProperties">' . PHP_EOL .
            '                                <xades:SignedSignatureProperties>' . PHP_EOL .
            '                                    <xades:SigningTime>SIGNING_TIME_PLACEHOLDER</xades:SigningTime>' . PHP_EOL .
            '                                    <xades:SigningCertificate>' . PHP_EOL .
            '                                        <xades:Cert>' . PHP_EOL .
            '                                            <xades:CertDigest>' . PHP_EOL .
            '                                                <ds:DigestMethod xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>' . PHP_EOL .
            '                                                <ds:DigestValue xmlns:ds="http://www.w3.org/2000/09/xmldsig#">DIGEST_PLACEHOLDER</ds:DigestValue>' . PHP_EOL .
            '                                            </xades:CertDigest>' . PHP_EOL .
            '                                            <xades:IssuerSerial>' . PHP_EOL .
            '                                                <ds:X509IssuerName xmlns:ds="http://www.w3.org/2000/09/xmldsig#">ISSUER_PLACEHOLDER</ds:X509IssuerName>' . PHP_EOL .
            '                                                <ds:X509SerialNumber xmlns:ds="http://www.w3.org/2000/09/xmldsig#">SERIAL_PLACEHOLDER</ds:X509SerialNumber>' . PHP_EOL .
            '                                            </xades:IssuerSerial>' . PHP_EOL .
            '                                        </xades:Cert>' . PHP_EOL .
            '                                    </xades:SigningCertificate>' . PHP_EOL .
            '                                </xades:SignedSignatureProperties>' . PHP_EOL .
            '                            </xades:SignedProperties>';
        return str_replace(
            [
                'SIGNING_TIME_PLACEHOLDER',
                'DIGEST_PLACEHOLDER',
                'ISSUER_PLACEHOLDER',
                'SERIAL_PLACEHOLDER',
            ],
            [
                $signingTime,
                $this->cert->getCertHash(),
                $this->cert->getFormattedIssuer(),
                $this->cert->getCurrentCert()['tbsCertificate']['serialNumber']->toString()
            ],
            $template
        );
    }

    /**
     * Sets the signature value.
     *
     * @param string $signatureValue
     *
     * @return self
     */
    public function setSignatureValue(string $signatureValue): self
    {
        $this->signatureValue = $signatureValue;
        return $this;
    }

    /**
     * Sets the invoice digest.
     *
     * @param string $invoiceDigest
     *
     * @return self
     */
    public function setInvoiceDigest(string $invoiceDigest): self
    {
        $this->invoiceDigest = $invoiceDigest;
        return $this;
    }

    /**
     * Sets the certificate.
     *
     * @param Certificate $certificate
     *
     * @return self
     */
    public function setCertificate(Certificate $certificate): self
    {
        $this->cert = $certificate;
        return $this;
    }
}