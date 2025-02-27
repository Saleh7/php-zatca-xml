<?php
namespace Saleh7\Zatca\Mappers\Validators;

/**
 * Class InvoiceValidator
 *
 * Validates the required fields for invoice data according to the 
 * 20210819_ZATCA_E-invoice_Validation_Rules.
 *
 * This validator ensures that all necessary fields are present and non-empty,
 * including top-level invoice fields, supplier and (if applicable) customer data,
 * payment means, tax totals, legal monetary totals, invoice lines, and additional
 * document references.
 *
 * @package Saleh7\Zatca\Mappers\Validators
 */
class InvoiceValidator
{
    /**
     * Validate the required invoice data fields.
     *
     * This method checks that the necessary fields such as 'uuid', 'id', 'issueDate',
     * 'currencyCode', 'taxCurrencyCode', supplier data, invoice lines, and other nested 
     * sections are present and not empty. Additionally, if the invoice is not "simplified",
     * customer data becomes mandatory.
     *
     * @param array $data The invoice data array.
     * @throws \InvalidArgumentException if any required field is missing or empty.
     */
    public function validate(array $data): void
    {
        // Validate top-level invoice fields.
        $requiredFields = [
            'uuid'            => 'UUID',
            'id'              => 'Invoice ID',
            'issueDate'       => 'Issue Date',
            'currencyCode'    => 'Invoice Currency Code',
            'taxCurrencyCode' => 'Tax Currency Code'
        ];
        foreach ($requiredFields as $field => $friendlyName) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("The field '{$friendlyName}' is required and cannot be empty.");
            }
        }
        
        // Validate invoiceType fields if provided.
        if (isset($data['invoiceType'])) {
            $invoiceTypeRequired = [
                'invoice' => 'Invoice Type (invoice)',
                'type'    => 'Invoice Type (type)'
            ];
            foreach ($invoiceTypeRequired as $field => $friendlyName) {
                if (!isset($data['invoiceType'][$field]) || empty($data['invoiceType'][$field])) {
                    throw new \InvalidArgumentException("The field '{$friendlyName}' in invoiceType is required and cannot be empty.");
                }
            }
        }
        
        // Validate supplier data.
        if (!isset($data['supplier']) || empty($data['supplier'])) {
            throw new \InvalidArgumentException("Supplier data is required.");
        } else {
            $supplierRequired = ['registrationName', 'taxId', 'address'];
            foreach ($supplierRequired as $field) {
                if (!isset($data['supplier'][$field]) || empty($data['supplier'][$field])) {
                    throw new \InvalidArgumentException("The field 'Supplier {$field}' is required and cannot be empty.");
                }
            }
            // Validate supplier address fields.
            $supplierAddressRequired = ['street', 'buildingNumber', 'city', 'postalZone', 'country'];
            foreach ($supplierAddressRequired as $field) {
                if (!isset($data['supplier']['address'][$field]) || empty($data['supplier']['address'][$field])) {
                    throw new \InvalidArgumentException("The field 'Supplier Address {$field}' is required and cannot be empty.");
                }
            }
        }
        
        // Determine invoice type: if not simplified, then customer data is required.
        $isSimplified = isset($data['invoiceType']['invoice']) && strtolower($data['invoiceType']['invoice']) === 'simplified';
        if (!$isSimplified) {
            // Validate customer data.
            if (!isset($data['customer']) || empty($data['customer'])) {
                throw new \InvalidArgumentException("Customer data is required for non-simplified invoices.");
            } else {
                $customerRequired = ['registrationName', 'taxId', 'address'];
                foreach ($customerRequired as $field) {
                    if (!isset($data['customer'][$field]) || empty($data['customer'][$field])) {
                        throw new \InvalidArgumentException("The field 'Customer {$field}' is required and cannot be empty.");
                    }
                }
                // Validate customer address fields.
                $customerAddressRequired = ['street', 'buildingNumber', 'city', 'postalZone', 'country'];
                foreach ($customerAddressRequired as $field) {
                    if (!isset($data['customer']['address'][$field]) || empty($data['customer']['address'][$field])) {
                        throw new \InvalidArgumentException("The field 'Customer Address {$field}' is required and cannot be empty.");
                    }
                }
            }
        }
        
        // Validate paymentMeans if provided.
        if (isset($data['paymentMeans'])) {
            if (!isset($data['paymentMeans']['code']) || empty($data['paymentMeans']['code'])) {
                throw new \InvalidArgumentException("The field 'Payment Means code' is required and cannot be empty.");
            }
        }
        
        // Validate taxTotal if provided.
        if (isset($data['taxTotal'])) {
            if (!isset($data['taxTotal']['taxAmount']) || $data['taxTotal']['taxAmount'] === '') {
                throw new \InvalidArgumentException("The field 'Tax Total taxAmount' is required and cannot be empty.");
            }
            // Validate subTotals if provided.
            if (isset($data['taxTotal']['subTotals']) && is_array($data['taxTotal']['subTotals'])) {
                foreach ($data['taxTotal']['subTotals'] as $index => $subTotal) {
                    $subRequired = ['taxableAmount', 'taxCategory'];
                    foreach ($subRequired as $field) {
                        if (!isset($subTotal[$field]) || empty($subTotal[$field])) {
                            throw new \InvalidArgumentException("The field 'Tax Total subTotals[{$index}] {$field}' is required and cannot be empty.");
                        }
                    }
                    // Validate taxScheme id in subTotal.
                    if (!isset($subTotal['taxCategory']['taxScheme']['id']) || empty($subTotal['taxCategory']['taxScheme']['id'])) {
                        throw new \InvalidArgumentException("The field 'Tax Total subTotals[{$index}] TaxScheme id' is required and cannot be empty.");
                    }
                }
            }
        }
        
        // Validate legalMonetaryTotal.
        if (!isset($data['legalMonetaryTotal']) || empty($data['legalMonetaryTotal'])) {
            throw new \InvalidArgumentException("Legal Monetary Total data is required.");
        } else {
            $lmtRequired = ['lineExtensionAmount', 'taxExclusiveAmount', 'taxInclusiveAmount', 'payableAmount'];
            foreach ($lmtRequired as $field) {
                if (!isset($data['legalMonetaryTotal'][$field]) || $data['legalMonetaryTotal'][$field] === '') {
                    throw new \InvalidArgumentException("The field 'Legal Monetary Total {$field}' is required and cannot be empty.");
                }
            }
        }
        
        // Validate invoiceLines.
        if (!isset($data['invoiceLines']) || !is_array($data['invoiceLines']) || count($data['invoiceLines']) === 0) {
            throw new \InvalidArgumentException("At least one invoice line is required.");
        } else {
            foreach ($data['invoiceLines'] as $lineIndex => $line) {
                $lineRequired = ['id', 'unitCode', 'quantity', 'lineExtensionAmount', 'item', 'price', 'taxTotal'];
                foreach ($lineRequired as $field) {
                    if (!isset($line[$field]) || $line[$field] === '') {
                        throw new \InvalidArgumentException("The field 'Invoice Lines[{$lineIndex}] {$field}' is required and cannot be empty.");
                    }
                }
                
                // Validate item within invoice line.
                if (!isset($line['item']['name']) || empty($line['item']['name'])) {
                    throw new \InvalidArgumentException("The field 'Invoice Lines[{$lineIndex}] Item name' is required and cannot be empty.");
                }
                if (!isset($line['item']['classifiedTaxCategory'][0]['taxScheme']) || !isset($line['item']['classifiedTaxCategory'][0]['taxScheme']['id']) || empty($line['item']['classifiedTaxCategory'][0]['taxScheme']['id'])) {
                    throw new \InvalidArgumentException("The field 'Invoice Lines[{$lineIndex}] Item TaxScheme id' is required and cannot be empty.");
                }
                if (!isset($line['item']['classifiedTaxCategory'][0]['percent']) || $line['item']['classifiedTaxCategory'][0]['percent'] === '') {
                    throw new \InvalidArgumentException("The field 'Invoice Lines[{$lineIndex}] Item percent' is required and cannot be empty.");
                }
                
                // Validate price within invoice line.
                if (!isset($line['price']['amount']) || $line['price']['amount'] === '') {
                    throw new \InvalidArgumentException("The field 'Invoice Lines[{$lineIndex}] Price amount' is required and cannot be empty.");
                }
                
                // Validate taxTotal within invoice line.
                if (!isset($line['taxTotal']['taxAmount']) || $line['taxTotal']['taxAmount'] === '') {
                    throw new \InvalidArgumentException("The field 'Invoice Lines[{$lineIndex}] TaxTotal taxAmount' is required and cannot be empty.");
                }
            }
        }
        
        // Validate additionalDocuments if provided.
        if (isset($data['additionalDocuments']) && is_array($data['additionalDocuments'])) {
            foreach ($data['additionalDocuments'] as $docIndex => $doc) {
                if (!isset($doc['id']) || empty($doc['id'])) {
                    throw new \InvalidArgumentException("The field 'AdditionalDocuments[{$docIndex}] id' is required and cannot be empty.");
                }
                // For documents with id 'PIH', attachment is required.
                if (($doc['id'] ?? '') === 'PIH') {
                    if (!isset($doc['attachment']) || empty($doc['attachment'])) {
                        throw new \InvalidArgumentException("The attachment for AdditionalDocuments[{$docIndex}] with id 'PIH' is required.");
                    }
                }
            }
        }
    }
}
