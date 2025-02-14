<?php

namespace Saleh7\Zatca;

use Saleh7\Zatca\Helpers\QRCodeGenerator;
use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\Helpers\InvoiceExtension;
use Saleh7\Zatca\Helpers\InvoiceSignatureBuilder;

class InvoiceSigner
{
    private $signedInvoice;  // Signed invoice XML string
    private $hash;           // Invoice hash (base64 encoded)
    private $qrCode;         // QR Code (base64 encoded)
    private $certificate;    // Certificate used for signing
    private $digitalSignature; // Digital signature (base64 encoded)

    // Private constructor to force usage of signInvoice method
    private function __construct() {}

    /**
     * Signs the invoice XML and returns an InvoiceSigner object.
     *
     * @param string      $xmlInvoice  Invoice XML as a string
     * @param Certificate $certificate Certificate for signing
     * @return self
     */
    public static function signInvoice(string $xmlInvoice, Certificate $certificate): self
    {
        $instance = new self();
        $instance->certificate = $certificate;

        // Convert XML string to DOM
        $xmlDom = InvoiceExtension::fromString($xmlInvoice);

        // Remove unwanted tags per guidelines
        $xmlDom->removeByXpath('ext:UBLExtensions');
        $xmlDom->removeByXpath('cac:Signature');
        $xmlDom->removeParentByXpath('cac:AdditionalDocumentReference/cbc:ID[. = "QR"]');

        // Compute hash using SHA-256
        $invoiceHashBinary = hash('sha256', $xmlDom->getElement()->C14N(false, false), true);
        $instance->hash = base64_encode($invoiceHashBinary);

        // Create digital signature using the private key
        $instance->digitalSignature = base64_encode(
            $certificate->getPrivateKey()->sign($invoiceHashBinary)
        );

        // Prepare UBL Extension with certificate, hash, and signature
        $ublExtension = (new InvoiceSignatureBuilder)
            ->setCertificate($certificate)
            ->setInvoiceDigest($instance->hash)
            ->setSignatureValue($instance->digitalSignature)
            ->buildSignatureXml();

        // Generate QR Code
        $instance->qrCode = QRCodeGenerator::createFromTags(
            $xmlDom->generateQrTagsArray($certificate, $instance->hash, $instance->digitalSignature)
        )->encodeBase64();


        // Insert UBL Extension and QR Code into the XML
        $signedInvoice = str_replace(
            [
                "<cbc:ProfileID>",
                '<cac:AccountingSupplierParty>',
            ],
            [
                "<ext:UBLExtensions>" . $ublExtension . "</ext:UBLExtensions>" . PHP_EOL . "    <cbc:ProfileID>",
                $instance->getQRNode($instance->qrCode) . PHP_EOL . "    <cac:AccountingSupplierParty>",
            ],
            $xmlDom->toXml()
        );

        // Remove extra blank lines and save
        $instance->signedInvoice = preg_replace('/^[ \t]*[\r\n]+/m', '', $signedInvoice);

        return $instance;
    }

    /**
     * Saves the signed invoice as an XML file.
     *
     * @param string $filename (Optional) File path to save the XML.
     * @return self
     */
    public function saveXMLFile(string $filename = 'signed_invoice.xml'): self
    {
        $outputDir = 'output';
        
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $fullPath = "{$outputDir}/{$filename}";

        if (file_put_contents($fullPath, $this->signedInvoice) === false) {
            throw new \Exception("Failed to save signed XML to file: {$fullPath}");
        }

        echo "Signed Invoice XML saved to: {$fullPath}\n";
        return $this;
    }

    /**
     * Returns the QR node string.
     *
     * @param string $QRCode
     * @return string
     */
    private function getQRNode(string $QRCode): string
    {
        return "<cac:AdditionalDocumentReference>
        <cbc:ID>QR</cbc:ID>
        <cac:Attachment>
            <cbc:EmbeddedDocumentBinaryObject mimeCode=\"text/plain\">$QRCode</cbc:EmbeddedDocumentBinaryObject>
        </cac:Attachment>
    </cac:AdditionalDocumentReference>";
    }

    /**
     * Get signed invoice XML.
     *
     * @return string
     */
    public function getInvoice(): string
    {
        return $this->signedInvoice;
    }

    /**
     * Get invoice hash.
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Get QR Code.
     *
     * @return string
     */
    public function getQRCode(): string
    {
        return $this->qrCode;
    }

    /**
     * Get the certificate used for signing.
     *
     * @return Certificate
     */
    public function getCertificate(): Certificate
    {
        return $this->certificate;
    }
}
