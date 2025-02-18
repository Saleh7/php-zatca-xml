<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Service;
use Saleh7\Zatca\Exceptions\ZatcaStorageException;

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
     * @param string|null $outputDir (Optional) Directory name. Set to null if $filename contains the full file path.
     * @return self
     * @throws ZatcaStorageException If the XML file cannot be saved.
     */
    public function saveXMLFile(string $filename = 'unsigned_invoice.xml', ?string $outputDir = 'output'): self
    {
        (new Storage($outputDir))->put($filename, $this->generatedXml);
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