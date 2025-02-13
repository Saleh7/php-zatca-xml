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

    /**
     * Generate the invoice XML.
     *
     * @param Invoice $invoice The invoice object to serialize.
     * @param string $currencyId Currency identifier. Default is 'SAR'.
     * @return string The generated XML string.
     */
    public static function invoice(Invoice $invoice, string $currencyId = 'SAR'): string
    {
        self::$currencyID = $currencyId;

        $xmlService = new Service();
        $xmlService->namespaceMap = [
            'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2' => '',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2' => 'cac',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2' => 'cbc',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2' => 'ext'
        ];

        return $xmlService->write('Invoice', [$invoice]);
    }
    public static function saveXMLToFile(string $xml, string $filename): void
    {
        $outputDir = 'output';

        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                throw new \Exception("Failed to create directory: {$outputDir}");
            }
        }

        $fullPath = $outputDir . '/' . $filename;
        if (file_put_contents($fullPath, $xml) === false) {
            throw new \Exception("Failed to write XML to file: {$fullPath}");
        }
        echo "Invoice XML saved to {$fullPath}\n";
    }
}
