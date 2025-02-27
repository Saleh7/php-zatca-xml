<?php
namespace Saleh7\Zatca\Mappers;

use Saleh7\Zatca\{
    InvoiceLine, TaxTotal
};

/**
 * Class InvoiceLineMapper
 *
 * Maps invoice line data (from an array) to an array of InvoiceLine objects.
 */
class InvoiceLineMapper
{
    /**
     * @var ItemMapper Mapper for converting item data.
     */
    private ItemMapper $itemMapper;

    /**
     * @var PriceMapper Mapper for converting price data.
     */
    private PriceMapper $priceMapper;

    /**
     * InvoiceLineMapper constructor.
     *
     * Initializes the dependent mappers.
     */
    public function __construct()
    {
        $this->itemMapper = new ItemMapper();
        $this->priceMapper = new PriceMapper();
    }

    /**
     * Map an array of invoice line data to an array of InvoiceLine objects.
     *
     * Expected input for each line:
     * [
     *   'id' => (string|int),
     *   'unitCode' => (string),
     *   'lineExtensionAmount' => (float),
     *   'quantity' => (int|float),
     *   'item' => [ ... ],     // Data for item mapping.
     *   'price' => [ ... ],    // Data for price mapping.
     *   'taxTotal' => [        // Data for tax total mapping.
     *       'taxAmount' => (float),
     *       'roundingAmount' => (float)
     *   ]
     * ]
     *
     * @param array $lines Array of invoice lines data.
     * @return InvoiceLine[] Array of mapped InvoiceLine objects.
     */
    public function mapInvoiceLines(array $lines): array
    {
        $invoiceLines = [];
        foreach ($lines as $line) {
            // Map item data using ItemMapper.
            $item = $this->itemMapper->map($line['item'] ?? []);
            // Map price data using PriceMapper.
            $price = $this->priceMapper->map($line['price'] ?? []);
            // Map line tax total data.
            $taxTotal = $this->mapLineTaxTotal($line['taxTotal'] ?? []);
            // Create and populate the InvoiceLine object.
            $invoiceLine = (new InvoiceLine())
                ->setUnitCode($line['unitCode'] ?? "PCE")
                ->setId((string)($line['id'] ?? '1'))
                ->setItem($item)
                ->setLineExtensionAmount($line['lineExtensionAmount'] ?? 0)
                ->setPrice($price)
                ->setTaxTotal($taxTotal)
                ->setInvoicedQuantity($line['quantity'] ?? 0);
            $invoiceLines[] = $invoiceLine;
        }
        return $invoiceLines;
    }

    /**
     * Map line tax total data to a TaxTotal object.
     *
     * Expected input:
     * [
     *   'taxAmount' => (float),
     *   'roundingAmount' => (float)
     * ]
     *
     * @param array $data Array of line tax total data.
     * @return TaxTotal The mapped TaxTotal object.
     */
    private function mapLineTaxTotal(array $data): TaxTotal
    {
        return (new TaxTotal())
            ->setTaxAmount($data['taxAmount'] ?? 0)
            ->setRoundingAmount($data['roundingAmount'] ?? 0);
    }
}
