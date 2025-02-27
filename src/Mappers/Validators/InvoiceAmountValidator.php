<?php
namespace Saleh7\Zatca\Mappers\Validators;

/**
 * Class InvoiceAmountValidator
 *
 * Validates the financial amounts in the invoice data including monetary totals,
 * tax amounts, and invoice lines to ensure correctness and consistency.
 *
 * This validator ensures that:
 * - Legal monetary totals are numeric, non-negative, and consistent.
 * - The taxInclusiveAmount is approximately equal to the sum of taxExclusiveAmount and taxTotal.
 * - Each invoice line has valid numeric values for quantity, price, and line extension amounts,
 *   and that calculations (such as price * quantity) are consistent with the provided amounts.
 *
 * @package Saleh7\Zatca\Mappers\Validators
 */
class InvoiceAmountValidator
{
    /**
     * Validate the monetary totals in the invoice.
     *
     * This method ensures that the legal monetary totals contain valid numeric values,
     * are non-negative, and that the taxInclusiveAmount approximately equals the sum of
     * taxExclusiveAmount and taxTotal.
     *
     * @param array $data The invoice data array.
     * @throws \InvalidArgumentException if any monetary field is missing, invalid, or inconsistent.
     */
    public function validateMonetaryTotals(array $data): void
    {
        // Check that the Legal Monetary Total section exists.
        if (!isset($data['legalMonetaryTotal'])) {
            throw new \InvalidArgumentException("Legal Monetary Total section is missing.");
        }

        $lmt = $data['legalMonetaryTotal'];
        $requiredFields = [
            'lineExtensionAmount',
            'taxExclusiveAmount',
            'taxInclusiveAmount',
            'payableAmount'
        ];

        // Ensure that required fields exist, are numeric, and non-negative.
        foreach ($requiredFields as $field) {
            if (!isset($lmt[$field]) || !is_numeric($lmt[$field])) {
                throw new \InvalidArgumentException("Legal Monetary Total field '{$field}' must be a numeric value.");
            }
            if ((float)$lmt[$field] < 0) {
                throw new \InvalidArgumentException("Legal Monetary Total field '{$field}' cannot be negative.");
            }
        }

        // Retrieve taxTotal amount if available.
        $taxTotalAmount = 0.0;
        if (isset($data['taxTotal']['taxAmount']) && is_numeric($data['taxTotal']['taxAmount'])) {
            $taxTotalAmount = (float)$data['taxTotal']['taxAmount'];
        }

        $taxExclusiveAmount = (float)$lmt['taxExclusiveAmount'];
        $expectedTaxInclusive = $taxExclusiveAmount + $taxTotalAmount;
        $actualTaxInclusive = (float)$lmt['taxInclusiveAmount'];
        
        // Allow a small difference (e.g., 0.01) due to rounding differences.
        if (abs($expectedTaxInclusive - $actualTaxInclusive) > 0.01) {
            throw new \InvalidArgumentException(
                "The taxInclusiveAmount ({$actualTaxInclusive}) does not equal taxExclusiveAmount ({$taxExclusiveAmount}) plus taxTotal ({$taxTotalAmount})."
            );
        }
    }

    /**
     * Validate invoice lines for numeric consistency and calculation correctness.
     *
     * This method checks that each invoice line has valid numeric values for key fields,
     * ensures that the values are non-negative, and verifies that:
     * - (price amount * quantity) equals the lineExtensionAmount.
     * - The taxTotal's taxAmount is valid and non-negative.
     * - The roundingAmount equals lineExtensionAmount plus taxTotal->taxAmount.
     *
     * @param array $invoiceLines Array of invoice lines.
     * @throws \InvalidArgumentException if any invoice line contains invalid numeric values or incorrect calculations.
     */
    public function validateInvoiceLines(array $invoiceLines): void
    {
        // Tolerance for floating point comparisons.
        $tolerance = 0.01;

        foreach ($invoiceLines as $index => $line) {
            // Validate that 'quantity' and 'lineExtensionAmount' are provided, numeric, and non-negative.
            $numericFields = ['quantity', 'lineExtensionAmount'];
            foreach ($numericFields as $field) {
                if (!isset($line[$field]) || !is_numeric($line[$field])) {
                    throw new \InvalidArgumentException("Invoice Line [{$index}] field '{$field}' must be a numeric value.");
                }
                if ((float)$line[$field] < 0) {
                    throw new \InvalidArgumentException("Invoice Line [{$index}] field '{$field}' cannot be negative.");
                }
            }

            // Validate that the price amount exists, is numeric, and non-negative.
            if (!isset($line['price']['amount']) || !is_numeric($line['price']['amount'])) {
                throw new \InvalidArgumentException("Invoice Line [{$index}] Price amount must be a numeric value.");
            }
            if ((float)$line['price']['amount'] < 0) {
                throw new \InvalidArgumentException("Invoice Line [{$index}] Price amount cannot be negative.");
            }

            // Calculate expected lineExtensionAmount = price amount * quantity.
            $priceAmount = (float)$line['price']['amount'];
            $quantity = (float)$line['quantity'];
            $expectedLineExtension = $priceAmount * $quantity;
            $providedLineExtension = (float)$line['lineExtensionAmount'];

            if (abs($expectedLineExtension - $providedLineExtension) > $tolerance) {
                throw new \InvalidArgumentException(
                    "Invoice Line [{$index}] lineExtensionAmount is incorrect. Expected {$expectedLineExtension}, got {$providedLineExtension}."
                );
            }

            // Validate item taxPercent if provided.
            if (isset($line['item']['taxPercent'])) {
                if (!is_numeric($line['item']['taxPercent'])) {
                    throw new \InvalidArgumentException("Invoice Line [{$index}] item taxPercent must be a numeric value.");
                }
                $taxPercent = (float)$line['item']['taxPercent'];
                if ($taxPercent < 0 || $taxPercent > 100) {
                    throw new \InvalidArgumentException("Invoice Line [{$index}] item taxPercent must be between 0 and 100.");
                }
            }

            // Validate that taxTotal's taxAmount exists, is numeric, and non-negative.
            if (!isset($line['taxTotal']['taxAmount']) || !is_numeric($line['taxTotal']['taxAmount'])) {
                throw new \InvalidArgumentException("Invoice Line [{$index}] TaxTotal taxAmount must be a numeric value.");
            }
            $taxLineAmount = (float)$line['taxTotal']['taxAmount'];
            if ($taxLineAmount < 0) {
                throw new \InvalidArgumentException("Invoice Line [{$index}] TaxTotal taxAmount cannot be negative.");
            }
            
            // Validate that taxTotal's roundingAmount exists, is numeric, and equals lineExtensionAmount + taxAmount.
            if (!isset($line['taxTotal']['roundingAmount']) || !is_numeric($line['taxTotal']['roundingAmount'])) {
                throw new \InvalidArgumentException("Invoice Line [{$index}] TaxTotal roundingAmount must be a numeric value.");
            }
            $roundingAmount = (float)$line['taxTotal']['roundingAmount'];
            $expectedRounding = $providedLineExtension + $taxLineAmount;
            if (abs($expectedRounding - $roundingAmount) > $tolerance) {
                throw new \InvalidArgumentException(
                    "Invoice Line [{$index}] roundingAmount is incorrect. Expected {$expectedRounding}, got {$roundingAmount}."
                );
            }
        }
    }
}