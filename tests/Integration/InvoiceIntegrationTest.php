<?php
use PHPUnit\Framework\TestCase;
use Saleh7\Zatca\Mappers\InvoiceMapper;
use Saleh7\Zatca\GeneratorInvoice;
/**
 * Class InvoiceIntegrationTest
 *
 * This integration test verifies the complete invoice generation flow:
 *  - Mapping invoice data to an Invoice object via InvoiceMapper.
 *  - Validating invoice data using the internal validators.
 *  - Generating the XML output from the Invoice object.
 *
 * The test ensures that all components work together correctly.
 */
class InvoiceIntegrationTest extends TestCase
{
    /**
     * Test the complete invoice generation flow.
     *
     * This test passes a full invoice data array, then:
     * 1. Maps the data to an Invoice object.
     * 2. Generates XML using the GeneratorInvoice.
     */
    public function testCompleteInvoiceGenerationFlow(): void
    {
        // Sample invoice data with all required fields and valid monetary calculations.
        $invoiceData = [
            'uuid' => '3cf5ee18-ee25-44ea-a444-2c37ba7f28be',
            'id' => 'INV-12345',
            'issueDate' => '2024-09-07 17:41:08',
            'issueTime' => '2024-09-07 17:41:08',
            'currencyCode' => 'SAR',
            'taxCurrencyCode' => 'SAR',
            'invoiceType' => [
                'invoice' => 'simplified', // simplified invoice type
                'type' => 'invoice',
                'isThirdParty' => false,
                'isNominal' => false,
                'isExport' => false,
                'isSummary' => false,
                'isSelfBilled' => false
            ],
            'additionalDocuments' => [
                [
                    'id' => 'ICV',
                    'uuid' => '10'
                ],
                [
                    'id' => 'PIH',
                    'attachment' => [
                        'content' => 'dummyBase64Content',
                        'mimeCode' => 'base64',
                        'mimeType' => 'text/plain'
                    ]
                ]
            ],
            'supplier' => [
                'registrationName' => 'Supplier Inc.',
                'taxId' => '1234567890',
                'identificationId' => 'SUPPLIER-001',
                'identificationType' => 'CRN',
                'taxScheme' => ['id' => 'VAT'],
                'address' => [
                    'street' => 'Main St',
                    'buildingNumber' => '123',
                    'subdivision' => 'Al-Murooj',
                    'city' => 'Riyadh',
                    'postalZone' => '12345',
                    'country' => 'SA'
                ]
            ],
            'customer' => [
                'registrationName' => 'Customer LLC',
                'taxId' => '0987654321',
                'taxScheme' => ['id' => 'VAT'],
                'address' => [
                    'street' => 'Second St',
                    'buildingNumber' => '456',
                    'subdivision' => 'Al-Murooj',
                    'city' => 'Jeddah',
                    'postalZone' => '54321',
                    'country' => 'SA'
                ]
            ],
            'paymentMeans' => [
                'code' => '10'
            ],
            'allowanceCharges' => [
                [
                    'isCharge' => false,
                    'reason' => 'discount',
                    'amount' => 0.00,
                    'taxCategories' => [
                        [
                            'percent' => 15,
                            'taxScheme' => ['id' => 'VAT']
                        ]
                    ]
                ]
            ],
            'taxTotal' => [
                'taxAmount' => 30,
                'subTotals' => [
                    [
                        'taxableAmount' => 200,
                        'taxAmount' => 30,
                        'percent' => 15,
                        'taxScheme' => ['id' => 'VAT']
                    ]
                ]
            ],
            'legalMonetaryTotal' => [
                'lineExtensionAmount' => 200,
                'taxExclusiveAmount' => 200,
                'taxInclusiveAmount' => 230,
                'payableAmount' => 230,
                'allowanceTotalAmount' => 0
            ],
            'invoiceLines' => [
                [
                    'id' => 1,
                    'unitCode' => 'PCE',
                    'quantity' => 2,
                    'lineExtensionAmount' => 200, // Calculated: 100 * 2 = 200
                    'item' => [
                        'name' => 'Product A',
                        'taxPercent' => 15,
                        'taxScheme' => ['id' => 'VAT']
                    ],
                    'price' => [
                        'amount' => 100,
                        'unitCode' => 'PCE'
                    ],
                    'taxTotal' => [
                        'taxAmount' => 30,
                        'roundingAmount' => 230  // Calculated: 200 + 30 = 230
                    ]
                ]
            ]
        ];

        // Map the invoice data to an Invoice object
        $mapper = new InvoiceMapper();
        $invoice = $mapper->mapToInvoice($invoiceData);
        $this->assertNotNull($invoice, 'Invoice object should not be null.');

        // Generate XML output from the Invoice object
        $xmlOutput = GeneratorInvoice::invoice($invoice)->getXML();
        $this->assertNotEmpty($xmlOutput, 'Generated XML should not be empty.');
    }
}
