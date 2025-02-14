<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Service;

/**
 * Class GeneratorInvoice
 *
 * Generates the XML representation of an invoice.
 */
class GeneratorInvoice
{
    /** @var string Currency identifier used in invoice amounts. */
    public static string $currencyID;

    /** @var string Holds the generated XML */
    private string $generatedXml;

    // Private constructor to force usage of signInvoice method
    private function __construct() {}

    /**
     * Generate the invoice XML.
     *
     * @param Invoice $invoice The invoice object to serialize.
     * @param string $currencyId Currency identifier. Default is 'SAR'.
     * @return self The instance of GeneratorInvoice.
     */
    public static function invoice(Invoice $invoice, string $currencyId = 'SAR'): self
    {
        $instance = new self();
        self::$currencyID = $currencyId;

        $xmlService = new Service();
        $xmlService->namespaceMap = [
            'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2' => '',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2' => 'cac',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2' => 'cbc',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2' => 'ext'
        ];

        // Store generated XML inside the instance
        $instance->generatedXml = $xmlService->write('Invoice', [$invoice]);

        return $instance;
    }

    /**
     * Saves the generated invoice as an XML file.
     *
     * @param string $filename (Optional) File path to save the XML.
     * @return self
     */
    public function saveXMLFile(string $filename = 'unsigned_invoice.xml'): self
    {
        $outputDir = 'output';
        
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $fullPath = "{$outputDir}/{$filename}";

        if (file_put_contents($fullPath, $this->generatedXml) === false) {
            throw new \Exception("Failed to save XML to file: {$fullPath}");
        }

        echo "Invoice XML saved to: {$fullPath}\n";
        return $this;
    }

    /**
     * Get the generated XML string.
     *
     * @return string
     */
    public function getXML(): string
    {
        return $this->generatedXml;
    }
}